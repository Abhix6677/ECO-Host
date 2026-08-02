#!/usr/bin/env bash
# ==============================================================================
# EcoHost CoCalc Engine - 24/7 Auto-Start & Zero Maintenance Daemon Script
# ==============================================================================

ECOHOST_MASTER_URL="${1:-http://localhost:8000}"
SECRET_KEY="ecohost_cocalc_secret_key_2026"

echo "[$(date)] Starting EcoHost 24/7 Engine Startup Sequence..."

# 1. Kill stale port 9000 processes
fuser -k 9000/tcp > /dev/null 2>&1
sleep 1

# 2. Launch Cloudflare Quick Tunnel in background
nohup cloudflared tunnel --url http://localhost:9000 > ~/tunnel.log 2>&1 &
echo "[$(date)] Cloudflare Tunnel started. Waiting for connection..."
sleep 5

# 3. Extract the active trycloudflare URL using Python 3
TUNNEL_URL=$(python3 -c "import re; f=open('/home/user/tunnel.log').read() if __import__('os').path.exists('/home/user/tunnel.log') else ''; m=re.findall(r'https://[a-zA-Z0-9-]+\.trycloudflare\.com', f); print(m[-1] if m else '')")

if [ -z "$TUNNEL_URL" ]; then
    echo "[$(date)] ⚠️ Cloudflare URL not found in log yet, retrying in 3s..."
    sleep 3
    TUNNEL_URL=$(python3 -c "import re; f=open('/home/user/tunnel.log').read() if __import__('os').path.exists('/home/user/tunnel.log') else ''; m=re.findall(r'https://[a-zA-Z0-9-]+\.trycloudflare\.com', f); print(m[-1] if m else '')")
fi

echo "[$(date)] 🌐 Active Cloudflare URL: $TUNNEL_URL"
echo "$TUNNEL_URL" > ~/active_url.txt

# 4. Launch EcoHost Python Webhook Receiver (Port 9000)
nohup python3 ~/deploy_receiver.py \
  --port 9000 \
  --secret "$SECRET_KEY" \
  --cloudflare-url "$TUNNEL_URL" \
  --ecohost-url "$ECOHOST_MASTER_URL" > ~/receiver.log 2>&1 &

echo "[$(date)] 🚀 EcoHost Receiver active on Port 9000 with URL: $TUNNEL_URL"

# 5. Try Auto-registering with EcoHost Master if reachable
if [ -n "$ECOHOST_MASTER_URL" ]; then
    curl -s -X POST "$ECOHOST_MASTER_URL/api/cocalc/register-node" \
         -H "Content-Type: application/json" \
         -d "{\"url\":\"$TUNNEL_URL\",\"secret\":\"$SECRET_KEY\"}" > /dev/null 2>&1 || true
fi

echo "[$(date)] ✅ Startup Sequence Complete. EcoHost Engine Running 24/7."
