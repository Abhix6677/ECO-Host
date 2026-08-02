#!/usr/bin/env python3
"""
EcoHost CoCalc Webhook Receiver
================================
Python 3.10+, standard library only.

Endpoints:
  GET  /health                  Health check
  POST /api/deploy              Deploy a website (multipart/form-data)
  DEL  /api/site/{site_uuid}    Delete a deployed site

Multipart (not Base64) is used for ZIP transfer because:
  - 33% smaller payload (no base64 encoding overhead)
  - Streams directly to disk-compatible bytes object
  - Standard HTTP file upload — no custom encoding/decoding
  - Works with any HTTP client (curl, Python requests, Laravel HTTP)

Run:
  python3 deploy_receiver.py --port 9000 --secret YOUR_SECRET

With Cloudflare URL (so the receiver includes it in deploy responses):
  python3 deploy_receiver.py --port 9000 --secret YOUR_SECRET \\
    --cloudflare-url https://xxxx.trycloudflare.com
"""

import argparse
import cgi
import io
import json
import logging
import os
import shutil
import sys
import zipfile
from http.server import BaseHTTPRequestHandler, HTTPServer
from pathlib import Path
from threading import Thread

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

DEFAULT_PORT   = 9000
DEFAULT_SECRET = "ecohost_cocalc_secret_key_2026"
BASE_DIR       = Path(os.path.expanduser("~/websites"))
PUBLIC_DIR     = BASE_DIR / "public_sites"

# Set at startup from --cloudflare-url argument
CLOUDFLARE_URL: str = ""
SECRET_TOKEN:   str = DEFAULT_SECRET

# ---------------------------------------------------------------------------
# Logging
# ---------------------------------------------------------------------------

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
    handlers=[logging.StreamHandler(sys.stdout)],
)
log = logging.getLogger("ecohost")


# ---------------------------------------------------------------------------
# MIME type map
# ---------------------------------------------------------------------------

MIME_TYPES = {
    ".html": "text/html; charset=utf-8",
    ".htm":  "text/html; charset=utf-8",
    ".css":  "text/css",
    ".js":   "application/javascript",
    ".mjs":  "application/javascript",
    ".json": "application/json",
    ".png":  "image/png",
    ".jpg":  "image/jpeg",
    ".jpeg": "image/jpeg",
    ".gif":  "image/gif",
    ".svg":  "image/svg+xml",
    ".ico":  "image/x-icon",
    ".webp": "image/webp",
    ".woff": "font/woff",
    ".woff2":"font/woff2",
    ".ttf":  "font/ttf",
    ".otf":  "font/otf",
    ".pdf":  "application/pdf",
    ".txt":  "text/plain; charset=utf-8",
    ".xml":  "application/xml",
}


# ---------------------------------------------------------------------------
# Request Handler
# ---------------------------------------------------------------------------

class ReceiverHandler(BaseHTTPRequestHandler):

    server_version = "EcoHostReceiver/1.0"
    protocol_version = "HTTP/1.1"

    # -----------------------------------------------------------------------
    # Internal helpers
    # -----------------------------------------------------------------------

    def _send(self, status: int, data: dict) -> None:
        body = json.dumps(data, indent=2).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.send_header("Connection", "close")
        self.end_headers()
        self.wfile.write(body)

    def _ok(self, data: dict) -> None:
        self._send(200, data)

    def _err(self, status: int, message: str) -> None:
        log.warning("HTTP %d — %s", status, message)
        self._send(status, {"status": "error", "message": message})

    def _auth(self) -> bool:
        return self.headers.get("X-EcoHost-Token", "") == SECRET_TOKEN

    def log_message(self, fmt, *args):
        # Route http.server access log through our logger
        log.info("%s - %s", self.address_string(), fmt % args)

    # -----------------------------------------------------------------------
    # GET
    # -----------------------------------------------------------------------

    def do_GET(self) -> None:
        path = self.path.split("?")[0]  # strip query string

        # --- Health check ---------------------------------------------------
        if path in ("/health", "/api/health"):
            self._ok({
                "status":        "ok",
                "server":        "EcoHost CoCalc Receiver",
                "storage":       str(BASE_DIR),
                "public_sites":  str(PUBLIC_DIR),
                "cloudflare":    CLOUDFLARE_URL or "(not configured)",
            })
            return

        # --- Site visitor logs check ---------------------------------------
        if path.startswith("/api/site/") and path.endswith("/logs"):
            parts = path.strip("/").split("/")
            if len(parts) == 4:
                site_uuid = parts[2]
                self._get_site_logs(site_uuid)
                return

        # --- Static file serving --------------------------------------------
        if path.startswith("/storage/sites/"):
            self._serve_static(path)
            return

        self._err(404, "Not found.")

    # -----------------------------------------------------------------------
    # POST /api/deploy
    # Accepts multipart/form-data with fields:
    #   user_id   (string)
    #   site_uuid (string)
    #   zip_file  (file)
    # -----------------------------------------------------------------------

    def do_POST(self) -> None:
        if self.path != "/api/deploy":
            self._err(404, "Not found.")
            return

        if not self._auth():
            self._err(401, "Unauthorized: invalid or missing X-EcoHost-Token.")
            return

        content_type = self.headers.get("Content-Type", "")

        if "multipart/form-data" not in content_type:
            self._err(415, "Expected multipart/form-data.")
            return

        # Parse multipart using cgi.FieldStorage (stdlib, works in Python 3.10)
        try:
            environ = {
                "REQUEST_METHOD": "POST",
                "CONTENT_TYPE":   content_type,
                "CONTENT_LENGTH": self.headers.get("Content-Length", "0"),
            }
            form = cgi.FieldStorage(
                fp=self.rfile,
                headers=self.headers,
                environ={"REQUEST_METHOD": "POST", "CONTENT_TYPE": content_type, "CONTENT_LENGTH": self.headers.get("Content-Length", "0")}
            )
        except Exception as exc:
            log.exception("Failed to parse multipart body")
            self._err(400, f"Multipart parse error: {exc}")
            return

        user_id   = (form.getvalue("user_id")   or "").strip()
        site_uuid = (form.getvalue("site_uuid") or "").strip()
        site_slug = (form.getvalue("site_slug") or site_uuid).strip()

        if not user_id or not site_uuid:
            self._err(400, "Missing required fields: user_id, site_uuid.")
            return

        if "/" in site_uuid or "\\" in site_uuid or ".." in site_uuid:
            self._err(400, "Invalid site_uuid value.")
            return

        if "/" in site_slug or "\\" in site_slug or ".." in site_slug:
            site_slug = site_uuid

        if "zip_file" not in form:
            self._err(400, "Missing file field: zip_file.")
            return

        zip_field = form["zip_file"]
        zip_bytes = zip_field.file.read() if hasattr(zip_field, "file") else b""

        if not zip_bytes:
            self._err(400, "zip_file is empty.")
            return

        log.info("Deploy request — user_id=%s site_uuid=%s site_slug=%s zip_size=%d bytes",
                 user_id, site_uuid, site_slug, len(zip_bytes))

        if not zipfile.is_zipfile(io.BytesIO(zip_bytes)):
            self._err(422, "Uploaded file is not a valid ZIP archive.")
            return

        user_dir   = BASE_DIR / f"user_{user_id}" / site_uuid
        public_dir = PUBLIC_DIR / site_uuid
        slug_dir   = PUBLIC_DIR / site_slug

        for d in (user_dir, public_dir):
            if d.exists():
                shutil.rmtree(d)
            d.mkdir(parents=True, exist_ok=True)

        if slug_dir.exists() and slug_dir != public_dir:
            try:
                if slug_dir.is_symlink():
                    slug_dir.unlink()
                else:
                    shutil.rmtree(slug_dir)
            except Exception:
                pass

        file_count = 0
        try:
            with zipfile.ZipFile(io.BytesIO(zip_bytes), "r") as zf:
                for member in zf.infolist():
                    name = member.filename
                    if (name.startswith("/") or ".." in name or
                        name.startswith("\\") or ":" in name):
                        continue

                    ext = os.path.splitext(name)[1].lower()
                    if ext in FORBIDDEN_EXTENSIONS:
                        continue

                    dest_file = (public_dir / name).resolve()
                    if not str(dest_file).startswith(str(public_dir.resolve())):
                        continue

                    if member.is_dir():
            shutil.rmtree(user_dir, ignore_errors=True)
            shutil.rmtree(public_dir, ignore_errors=True)
            log.exception("ZIP extraction failed")
            self._err(500, f"Extraction error: {exc}")
            return

        # Auto-unwrap single top-level directory if index.html is not at root (e.g. GitHub archive `reponame-main/`)
        for target_dir in (user_dir, public_dir):
            if not (target_dir / "index.html").exists():
                items = [item for item in target_dir.iterdir()]
                if len(items) == 1 and items[0].is_dir():
                    single_sub = items[0]
                    if (single_sub / "index.html").exists():
                        log.info("Auto-unwrapping top-level folder in %s: %s", target_dir.name, single_sub.name)
                        for child in single_sub.iterdir():
                            shutil.move(str(child), str(target_dir / child.name))
                        shutil.rmtree(single_sub)

        # Validate index.html at root of public_dir
        if not (public_dir / "index.html").exists():
            shutil.rmtree(user_dir, ignore_errors=True)
            shutil.rmtree(public_dir, ignore_errors=True)
            self._err(422, "ZIP does not contain index.html at the root level.")
            return

        log.info("Extracted %d files → %s", file_count, public_dir)

        # --- Build live URL --------------------------------------------------
        if CLOUDFLARE_URL:
            live_url = f"{CLOUDFLARE_URL}/storage/sites/{site_uuid}/"
        else:
            live_url = None

        response: dict = {
            "status":     "success",
            "message":    "Site deployed successfully on CoCalc Ubuntu.",
            "site_uuid":  site_uuid,
            "user_id":    user_id,
            "file_count": file_count,
            "cocalc_path":    str(public_dir),
            "public_url_path": f"/storage/sites/{site_uuid}/",
        }

        if live_url:
            response["live_url"] = live_url
        else:
            response["live_url"] = None
            response["tunnel_warning"] = (
                "Cloudflare tunnel URL is not configured. "
                "Restart the receiver with --cloudflare-url https://xxxx.trycloudflare.com"
            )

        self._ok(response)

    # -----------------------------------------------------------------------
    # DELETE /api/site/{site_uuid}
    # -----------------------------------------------------------------------

    def do_DELETE(self) -> None:
        prefix = "/api/site/"

        if not self.path.startswith(prefix):
            self._err(404, "Not found.")
            return

        if not self._auth():
            self._err(401, "Unauthorized: invalid or missing X-EcoHost-Token.")
            return

        site_uuid = self.path[len(prefix):].strip("/")

        if not site_uuid:
            self._err(400, "Missing site_uuid in URL.")
            return

        if "/" in site_uuid or "\\" in site_uuid or ".." in site_uuid:
            self._err(400, "Invalid site_uuid.")
            return

        deleted = []

        # Remove public_sites/{site_uuid}
        pub = PUBLIC_DIR / site_uuid
        if pub.exists():
            shutil.rmtree(pub)
            deleted.append(str(pub))
            log.info("Deleted public dir: %s", pub)

        # Remove user_*/{site_uuid}
        for user_dir in BASE_DIR.glob("user_*"):
            target = user_dir / site_uuid
            if target.exists():
                shutil.rmtree(target)
                deleted.append(str(target))
                log.info("Deleted user dir: %s", target)

        self._ok({
            "status":        "success",
            "message":       f"Deleted all files for site {site_uuid}.",
            "site_uuid":     site_uuid,
            "deleted_paths": deleted,
        })

    # -----------------------------------------------------------------------
    # Get live site access/traffic logs
    # -----------------------------------------------------------------------

    def _get_site_logs(self, site_uuid: str) -> None:
        if not self._auth():
            self._err(401, "Unauthorized: invalid or missing X-EcoHost-Token.")
            return

        log_file = PUBLIC_DIR / site_uuid / ".access.log"
        entries  = []

        if log_file.exists():
            try:
                lines = log_file.read_text(encoding="utf-8", errors="ignore").splitlines()
                entries = lines[-100:]  # Return last 100 HTTP traffic hits
            except Exception as exc:
                log.warning("Could not read access log for %s: %s", site_uuid, exc)

        self._ok({
            "status":    "success",
            "site_uuid": site_uuid,
            "logs":      entries,
        })

    # -----------------------------------------------------------------------
    # Static file server — GET /storage/sites/{site_uuid}/path/to/file
    # -----------------------------------------------------------------------

    def _serve_static(self, url_path: str) -> None:
        if url_path.startswith("/storage/sites/"):
            prefix = "/storage/sites/"
        elif url_path.startswith("/site/"):
            prefix = "/site/"
        elif url_path.startswith("/s/"):
            prefix = "/s/"
        else:
            prefix = "/storage/sites/"

        rel       = url_path[len(prefix):].lstrip("/")
        parts     = rel.split("/", 1)
        site_uuid = parts[0]
        sub_path  = parts[1] if len(parts) > 1 and parts[1] else "index.html"

        if not sub_path or sub_path.endswith("/"):
            sub_path = sub_path.rstrip("/") + "/index.html"

        file_path = (PUBLIC_DIR / site_uuid / sub_path).resolve()

        # Security: stay inside PUBLIC_DIR
        try:
            file_path.relative_to(PUBLIC_DIR.resolve())
        except ValueError:
            self._err(403, "Forbidden.")
            return

        if not file_path.exists() or not file_path.is_file():
            self._record_access_log(site_uuid, sub_path, 404)
            self._err(404, f"File not found: {sub_path}")
            return

        content      = file_path.read_bytes()
        content_type = MIME_TYPES.get(file_path.suffix.lower(), "application/octet-stream")

        self._record_access_log(site_uuid, sub_path, 200)

        self.send_response(200)
        self.send_header("Content-Type", content_type)
        self.send_header("Content-Length", str(len(content)))
        self.send_header("Cache-Control", "public, max-age=3600")
        self.send_header("Connection", "close")
        self.end_headers()
        self.wfile.write(content)

    def _record_access_log(self, site_uuid: str, sub_path: str, status_code: int) -> None:
        import datetime
        try:
            site_dir = PUBLIC_DIR / site_uuid
            if not site_dir.exists():
                return
            log_file = site_dir / ".access.log"
            now_str  = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            client_ip = self.client_address[0] if self.client_address else "127.0.0.1"
            log_line = f"[{now_str}] HTTP GET /{sub_path} - {status_code} OK - Visitor IP: {client_ip}\n"
            with open(log_file, "a", encoding="utf-8") as f:
                f.write(log_line)
        except Exception:
            pass


# ---------------------------------------------------------------------------
# Threaded HTTP server (handles concurrent requests)
# ---------------------------------------------------------------------------

class ThreadedHTTPServer(HTTPServer):
    """Handle each request in its own thread."""

    def process_request(self, request, client_address):
        t = Thread(target=self._process_request_thread,
                   args=(request, client_address))
        t.daemon = True
        t.start()

    def _process_request_thread(self, request, client_address):
        try:
            self.finish_request(request, client_address)
        except Exception:
            self.handle_error(request, client_address)
        finally:
            self.shutdown_request(request)


# ---------------------------------------------------------------------------
# Entry point
# ---------------------------------------------------------------------------

def main() -> None:
    global SECRET_TOKEN, CLOUDFLARE_URL

    parser = argparse.ArgumentParser(
        description="EcoHost CoCalc Webhook Receiver",
        formatter_class=argparse.ArgumentDefaultsHelpFormatter,
    )
    parser.add_argument(
        "--port", type=int,
        default=int(os.environ.get("RECEIVER_PORT", DEFAULT_PORT)),
        help="TCP port to listen on",
    )
    parser.add_argument(
        "--secret", type=str,
        default=os.environ.get("ECOHOST_SECRET", DEFAULT_SECRET),
        help="X-EcoHost-Token shared secret",
    )
    parser.add_argument(
        "--cloudflare-url", type=str,
        default=os.environ.get("CLOUDFLARE_TUNNEL_URL", ""),
        help="Public Cloudflare tunnel URL (e.g. https://xxxx.trycloudflare.com)",
    )
    parser.add_argument(
        "--ecohost-url", type=str,
        default=os.environ.get("ECOHOST_APP_URL", ""),
        help="EcoHost master application URL for auto-registering new Cloudflare URL (e.g. http://127.0.0.1:8000)",
    )
    args = parser.parse_args()

    SECRET_TOKEN   = args.secret
    CLOUDFLARE_URL = args.cloudflare_url.rstrip("/") if args.cloudflare_url else ""
    ECOHOST_URL    = args.ecohost_url.rstrip("/") if args.ecohost_url else ""

    # Ensure storage directories exist
    BASE_DIR.mkdir(parents=True, exist_ok=True)
    PUBLIC_DIR.mkdir(parents=True, exist_ok=True)

    cf_display = CLOUDFLARE_URL if CLOUDFLARE_URL else "⚠️  NOT SET — redeploy will lack a live URL"

    log.info("=" * 56)
    log.info(" EcoHost CoCalc Receiver")
    log.info("-" * 56)
    log.info(" Port           : %d", args.port)
    log.info(" Storage        : %s", BASE_DIR)
    log.info(" Public sites   : %s", PUBLIC_DIR)
    log.info(" Secret         : %s***", SECRET_TOKEN[:4])
    log.info(" Cloudflare URL : %s", cf_display)
    if ECOHOST_URL:
        log.info(" EcoHost Master : %s", ECOHOST_URL)
    log.info("=" * 56)

    # Auto-register Cloudflare URL with EcoHost Master if requested
    if ECOHOST_URL and CLOUDFLARE_URL:
        def auto_register():
            import urllib.request
            try:
                reg_endpoint = f"{ECOHOST_URL}/api/cocalc/register-node"
                payload = json.dumps({"url": CLOUDFLARE_URL, "secret": SECRET_TOKEN}).encode("utf-8")
                req = urllib.request.Request(reg_endpoint, data=payload, headers={"Content-Type": "application/json"})
                with urllib.request.urlopen(req, timeout=10) as resp:
                    res_body = json.loads(resp.read().decode("utf-8"))
                    log.info("✅ EcoHost Master Auto-Register Result: %s", res_body.get("message"))
            except Exception as ex:
                log.warning("⚠️ EcoHost Master Auto-Register Warning: %s", ex)

        Thread(target=auto_register, daemon=True).start()

    server = ThreadedHTTPServer(("0.0.0.0", args.port), ReceiverHandler)

    try:
        server.serve_forever()
    except KeyboardInterrupt:
        log.info("Shutting down.")
        server.server_close()


if __name__ == "__main__":
    main()
