#!/usr/bin/env bash
# ==============================================================================
# EcoHost CoCalc Engine - 24/7 Auto-Start & Zero Maintenance Daemon Script
# Uses localhost.run SSH tunnel + kvdb.io for FULLY AUTOMATIC URL sharing!
# Zero manual steps — URL auto-updates even after restart!
# ==============================================================================

ECOHOST_MASTER_URL="${1:-http://localhost:8000}"
SECRET_KEY="ecohost_cocalc_secret_key_2026"
SSH_KEY="$HOME/.ssh/ecohost_tunnel_key"
KVDB_BUCKET="HSTd8BeDfsQhDnjA6ooYoX"   # Free KV store — EcoHost reads from here!

echo "[$(date)] Starting EcoHost 24/7 Engine Startup Sequence..."

# 1. Kill stale port 9000 processes
fuser -k 9000/tcp > /dev/null 2>&1
sleep 1

# 2. Kill old tunnel processes
pkill -f "cloudflared" > /dev/null 2>&1
pkill -f "localhost.run" > /dev/null 2>&1
pkill -f "serveo.net" > /dev/null 2>&1
pkill -f "ngrok" > /dev/null 2>&1
sleep 1

# 3. Launch Cloudflare Tunnel in background (rock-solid 24/7 tunnel)
echo "[$(date)] 🌐 Starting Cloudflare Tunnel..."
nohup cloudflared tunnel --url http://localhost:9000 > ~/tunnel.log 2>&1 &

echo "[$(date)] Waiting for Cloudflare Tunnel URL..."
sleep 6

# 4. Extract Cloudflare URL using Python 3 (bypasses broken system grep)
TUNNEL_URL=$(python3 -c "
import re, time
for _ in range(12):
    try:
        data = open('/home/user/tunnel.log').read()
        m = re.findall(r'https://[a-zA-Z0-9\-]+\.trycloudflare\.com', data)
        if m:
            print(m[-1])
            break
    except:
        pass
    time.sleep(1)
")

if [ -z "$TUNNEL_URL" ]; then
    echo "[$(date)] ⚠️ Could not extract Cloudflare URL. Check ~/tunnel.log"
    cat ~/tunnel.log
    exit 1
fi

echo "[$(date)] 🌐 Active Tunnel URL: $TUNNEL_URL"
echo "$TUNNEL_URL" > ~/active_url.txt

# 7. ✅ AUTO-PUBLISH: Push new URL to GitHub so EcoHost auto-discovers it!
echo "[$(date)] 📡 Publishing URL to GitHub registry..."
echo "$TUNNEL_URL" > ~/ECO-Host/cocalc/live_url.txt
cd ~/ECO-Host
git add cocalc/live_url.txt
git commit -m "[auto] Update CoCalc receiver URL: $TUNNEL_URL" > /dev/null 2>&1 || true
git pull origin master --rebase > /dev/null 2>&1 || true
git push origin master > /dev/null 2>&1 && echo "[$(date)] ✅ URL published to GitHub! EcoHost will auto-discover: $TUNNEL_URL" || echo "[$(date)] ⚠️ GitHub push failed, EcoHost may use stale URL"
cd ~

# 8. Launch EcoHost Python Webhook Receiver (Port 9000)
nohup python3 ~/deploy_receiver.py \
  --port 9000 \
  --secret "$SECRET_KEY" \
  --cloudflare-url "$TUNNEL_URL" \
  --ecohost-url "$ECOHOST_MASTER_URL" > ~/receiver.log 2>&1 &

echo "[$(date)] 🚀 EcoHost Receiver active on Port 9000 with URL: $TUNNEL_URL"
echo "[$(date)] ✅ Startup Complete. EcoHost Engine Running 24/7."
