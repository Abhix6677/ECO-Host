#!/usr/bin/env bash
# ==============================================================================
# EcoHost CoCalc Engine - 24/7 Auto-Start & Zero Maintenance Daemon Script
# Uses ngrok STATIC domain — URL never changes, even after restart!
# ==============================================================================

ECOHOST_MASTER_URL="${1:-http://localhost:8000}"
SECRET_KEY="ecohost_cocalc_secret_key_2026"
NGROK_STATIC_DOMAIN="paramedic-autism-suggest.ngrok-free.app"
FIXED_URL="https://${NGROK_STATIC_DOMAIN}"

echo "[$(date)] Starting EcoHost 24/7 Engine Startup Sequence..."

# 1. Kill stale port 9000 processes
fuser -k 9000/tcp > /dev/null 2>&1
sleep 1

# 2. Kill any old ngrok/cloudflared instances
pkill -f ngrok > /dev/null 2>&1
pkill -f cloudflared > /dev/null 2>&1
sleep 1

# 3. Launch ngrok with STATIC domain (permanent URL — never changes!)
nohup ngrok http --url="${NGROK_STATIC_DOMAIN}" 9000 > ~/ngrok.log 2>&1 &
echo "[$(date)] ngrok tunnel started with static domain: ${NGROK_STATIC_DOMAIN}"
sleep 4

# 4. Launch EcoHost Python Webhook Receiver (Port 9000)
nohup python3 ~/deploy_receiver.py \
  --port 9000 \
  --secret "$SECRET_KEY" \
  --cloudflare-url "$FIXED_URL" \
  --ecohost-url "$ECOHOST_MASTER_URL" > ~/receiver.log 2>&1 &

echo "[$(date)] 🌐 Permanent URL: $FIXED_URL"
echo "$FIXED_URL" > ~/active_url.txt

echo "[$(date)] 🚀 EcoHost Receiver active on Port 9000 with URL: $FIXED_URL"

# 5. Try Auto-registering with EcoHost Master if reachable
if [ -n "$ECOHOST_MASTER_URL" ]; then
    sleep 2
    curl -s -X POST "$ECOHOST_MASTER_URL/api/cocalc/register-node" \
         -H "Content-Type: application/json" \
         -d "{\"url\":\"$FIXED_URL\",\"secret\":\"$SECRET_KEY\"}" > /dev/null 2>&1 || true
fi

echo "[$(date)] ✅ Startup Complete. EcoHost Engine Running 24/7 on: $FIXED_URL"
