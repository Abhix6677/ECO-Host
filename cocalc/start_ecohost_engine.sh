#!/usr/bin/env bash
# ==============================================================================
# EcoHost CoCalc Engine - 24/7 Auto-Start & Zero Maintenance Daemon Script
# Uses localhost.run SSH tunnel — PERMANENT URL via SSH key fingerprint!
# ==============================================================================

ECOHOST_MASTER_URL="${1:-http://localhost:8000}"
SECRET_KEY="ecohost_cocalc_secret_key_2026"
SSH_KEY="$HOME/.ssh/ecohost_tunnel_key"

echo "[$(date)] Starting EcoHost 24/7 Engine Startup Sequence..."

# 1. Kill stale port 9000 processes
fuser -k 9000/tcp > /dev/null 2>&1
sleep 1

# 2. Kill old tunnel processes
pkill -f "localhost.run" > /dev/null 2>&1
pkill -f "ssh.*localhost.run" > /dev/null 2>&1
pkill -f "ngrok" > /dev/null 2>&1
sleep 1

# 3. Generate dedicated SSH key if not exists (gives PERMANENT URL)
if [ ! -f "$SSH_KEY" ]; then
    echo "[$(date)] 🔑 Generating permanent SSH tunnel key..."
    ssh-keygen -t rsa -b 2048 -N "" -f "$SSH_KEY" -q
    echo "[$(date)] ✅ SSH key created: $SSH_KEY"
fi

# 4. Add localhost.run to known_hosts (avoid interactive prompt)
ssh-keyscan -H localhost.run >> ~/.ssh/known_hosts 2>/dev/null

# 5. Launch localhost.run SSH tunnel (permanent URL based on SSH key)
echo "[$(date)] 🌐 Starting localhost.run permanent tunnel..."
nohup ssh -i "$SSH_KEY" \
    -o StrictHostKeyChecking=no \
    -o ServerAliveInterval=30 \
    -o ServerAliveCountMax=10 \
    -o ExitOnForwardFailure=yes \
    -R 80:localhost:9000 \
    nokey@localhost.run 2>&1 | tee ~/tunnel.log &

echo "[$(date)] Waiting for tunnel URL..."
sleep 6

# 6. Extract tunnel URL from log using Python3
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
else
    echo "[$(date)] 🌐 Permanent Tunnel URL: $TUNNEL_URL"
    echo "$TUNNEL_URL" > ~/active_url.txt
fi

# 7. Launch EcoHost Python Webhook Receiver (Port 9000)
nohup python3 ~/deploy_receiver.py \
  --port 9000 \
  --secret "$SECRET_KEY" \
  --cloudflare-url "${TUNNEL_URL}" \
  --ecohost-url "$ECOHOST_MASTER_URL" > ~/receiver.log 2>&1 &

echo "[$(date)] 🚀 EcoHost Receiver active on Port 9000 with URL: $TUNNEL_URL"

# 8. Auto-register with EcoHost Master
if [ -n "$TUNNEL_URL" ]; then
    sleep 2
    curl -s -X POST "$ECOHOST_MASTER_URL/api/cocalc/register-node" \
         -H "Content-Type: application/json" \
         -d "{\"url\":\"$TUNNEL_URL\",\"secret\":\"$SECRET_KEY\"}" > /dev/null 2>&1 || true
fi

echo "[$(date)] ✅ Startup Complete. EcoHost Engine Running 24/7 on: $TUNNEL_URL"
