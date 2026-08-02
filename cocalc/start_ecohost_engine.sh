#!/usr/bin/env bash
# ==============================================================================
# EcoHost CoCalc Engine - 24/7 Auto-Start & Zero Maintenance Daemon Script
# Uses serveo.net SSH tunnel — PERMANENT CUSTOM SUBDOMAIN, no signup needed!
# ==============================================================================

ECOHOST_MASTER_URL="${1:-http://localhost:8000}"
SECRET_KEY="ecohost_cocalc_secret_key_2026"
SERVEO_SUBDOMAIN="ecohost-abhix"
FIXED_URL="https://${SERVEO_SUBDOMAIN}.serveo.net"

echo "[$(date)] Starting EcoHost 24/7 Engine Startup Sequence..."

# 1. Kill stale port 9000 processes
fuser -k 9000/tcp > /dev/null 2>&1
sleep 1

# 2. Kill any old tunnel processes
pkill -f "serveo.net" > /dev/null 2>&1
pkill -f "localhost.run" > /dev/null 2>&1
pkill -f "ngrok" > /dev/null 2>&1
sleep 1

# 3. Add serveo.net to known_hosts to avoid interactive prompt
ssh-keyscan -H serveo.net >> ~/.ssh/known_hosts 2>/dev/null

# 4. Launch serveo.net SSH tunnel with FIXED custom subdomain (permanent!)
#    URL will ALWAYS be: https://ecohost-abhix.serveo.net
echo "[$(date)] 🌐 Starting serveo.net permanent tunnel..."
nohup ssh -o StrictHostKeyChecking=no \
    -o ServerAliveInterval=30 \
    -o ServerAliveCountMax=10 \
    -o ExitOnForwardFailure=yes \
    -R "${SERVEO_SUBDOMAIN}:80:localhost:9000" \
    serveo.net > ~/tunnel.log 2>&1 &

echo "[$(date)] Waiting for tunnel to connect..."
sleep 5

# 5. Verify tunnel is active
TUNNEL_CHECK=$(python3 -c "
import urllib.request, json
try:
    r = urllib.request.urlopen('https://${SERVEO_SUBDOMAIN}.serveo.net/health', timeout=5)
    print('ok')
except:
    print('connecting')
" 2>/dev/null)

echo "[$(date)] 🌐 Permanent Tunnel URL: $FIXED_URL"
echo "$FIXED_URL" > ~/active_url.txt

# 6. Launch EcoHost Python Webhook Receiver (Port 9000)
nohup python3 ~/deploy_receiver.py \
  --port 9000 \
  --secret "$SECRET_KEY" \
  --cloudflare-url "$FIXED_URL" \
  --ecohost-url "$ECOHOST_MASTER_URL" > ~/receiver.log 2>&1 &

echo "[$(date)] 🚀 EcoHost Receiver active on Port 9000 with URL: $FIXED_URL"

# 7. Auto-register with EcoHost Master
sleep 3
curl -s -X POST "$ECOHOST_MASTER_URL/api/cocalc/register-node" \
     -H "Content-Type: application/json" \
     -d "{\"url\":\"$FIXED_URL\",\"secret\":\"$SECRET_KEY\"}" > /dev/null 2>&1 || true

echo "[$(date)] ✅ Startup Complete. EcoHost Engine Running 24/7 on: $FIXED_URL"
