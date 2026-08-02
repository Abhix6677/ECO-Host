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
pkill -f "localhost.run" > /dev/null 2>&1
pkill -f "serveo.net" > /dev/null 2>&1
pkill -f "ngrok" > /dev/null 2>&1
sleep 1

# 3. Generate dedicated SSH key once (same key = more consistent URL on localhost.run)
if [ ! -f "$SSH_KEY" ]; then
    echo "[$(date)] 🔑 Generating permanent SSH tunnel key..."
    ssh-keygen -t rsa -b 2048 -N "" -f "$SSH_KEY" -q
    echo "[$(date)] ✅ SSH key created: $SSH_KEY"
fi

# 4. Add known_hosts to avoid interactive prompt
ssh-keyscan -H localhost.run >> ~/.ssh/known_hosts 2>/dev/null

# 5. Launch localhost.run SSH tunnel with dedicated key
echo "[$(date)] 🌐 Starting tunnel..."
nohup ssh -i "$SSH_KEY" \
    -o StrictHostKeyChecking=no \
    -o ServerAliveInterval=30 \
    -o ServerAliveCountMax=10 \
    -R 80:localhost:9000 \
    nokey@localhost.run > ~/tunnel.log 2>&1 &

echo "[$(date)] Waiting for tunnel URL..."
sleep 7

# 6. Extract tunnel URL using Python3
TUNNEL_URL=$(python3 -c "
import re, time
for _ in range(10):
    try:
        data = open('/home/user/tunnel.log').read()
        m = re.findall(r'https://[a-zA-Z0-9\-]+\.lhr\.life', data)
        if m:
            print(m[-1])
            break
    except:
        pass
    time.sleep(1)
")

if [ -z "$TUNNEL_URL" ]; then
    echo "[$(date)] ⚠️ Could not extract tunnel URL. Check ~/tunnel.log"
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
