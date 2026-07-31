#!/bin/bash
# ==============================================================================
# EcoHost CoCalc Ubuntu Receiver Auto-Start Setup Script
# ==============================================================================
# This script sets up automatic startup and recovery for deploy_receiver.py
# inside your CoCalc Ubuntu Container.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RECEIVER_SCRIPT="$SCRIPT_DIR/deploy_receiver.py"
LOG_FILE="$HOME/ecohost_receiver.log"
PORT=9000
SECRET="ecohost_cocalc_secret_key_2026"

echo "=================================================="
echo " 🚀 Setting up EcoHost CoCalc Receiver Auto-Start"
echo "=================================================="

# Check Python 3
if ! command -v python3 &> /dev/null; then
    echo "❌ Error: Python 3 is not installed on this Ubuntu system."
    exit 1
fi

# Ensure deploy_receiver.py has executable permissions
chmod +x "$RECEIVER_SCRIPT"

# 1. Kill any existing receiver instance
pkill -f "deploy_receiver.py" 2>/dev/null || true

# 2. Add @reboot entry to user crontab for automatic recovery on container restart
CRON_CMD="@reboot /usr/bin/python3 $RECEIVER_SCRIPT --port $PORT --secret $SECRET >> $LOG_FILE 2>&1 &"

(crontab -l 2>/dev/null | grep -v "deploy_receiver.py"; echo "$CRON_CMD") | crontab -

echo "✅ Crontab reboot auto-recovery configured."

# 3. Start the receiver in background now
nohup python3 "$RECEIVER_SCRIPT" --port $PORT --secret $SECRET >> "$LOG_FILE" 2>&1 &

sleep 2

# Check if process is running
if pgrep -f "deploy_receiver.py" > /dev/null; then
    echo "✅ EcoHost Receiver is running on http://localhost:$PORT"
    echo "📋 Logs: $LOG_FILE"
else
    echo "❌ Failed to start receiver. Check $LOG_FILE for details."
fi
