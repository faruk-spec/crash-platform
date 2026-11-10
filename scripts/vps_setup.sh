#!/usr/bin/env bash
# ==============================================================================
# Autonomous Crash Platform - Ubuntu VPS & aaPanel Automated Provisioning
# Target: Ubuntu 22.04 / 24.04 LTS with aaPanel / Apache / PHP 8.3+ / MySQL 8.0
# ==============================================================================

set -e

echo "=== [1/5] Initializing Directory Structure ==="
BASE_DIR="/www/wwwroot/crash-platform"
mkdir -p "$BASE_DIR/releases"
mkdir -p "$BASE_DIR/shared/storage/app/public"
mkdir -p "$BASE_DIR/shared/storage/framework/cache"
mkdir -p "$BASE_DIR/shared/storage/framework/sessions"
mkdir -p "$BASE_DIR/shared/storage/framework/views"
mkdir -p "$BASE_DIR/shared/storage/logs"

echo "=== [2/5] Setting Up Permissions ==="
# Determine web server user (aaPanel uses 'www', Ubuntu standard uses 'www-data')
if id "www" &>/dev/null; then
    WEB_USER="www"
    WEB_GROUP="www"
elif id "www-data" &>/dev/null; then
    WEB_USER="www-data"
    WEB_GROUP="www-data"
else
    WEB_USER="$USER"
    WEB_GROUP="$USER"
fi

chown -R "$WEB_USER:$WEB_GROUP" "$BASE_DIR"
chmod -R 775 "$BASE_DIR/shared/storage"

echo "=== [3/5] Setting Up Persistent .env File ==="
if [ ! -f "$BASE_DIR/shared/.env" ]; then
    cat << 'EOF' > "$BASE_DIR/shared/.env"
APP_NAME="Autonomous Crash Control Center"
APP_ENV=production
APP_KEY=base64:u3wR6B5U4Yl8FwI3k8c2L0M9P1q7R4S2T6U8V0W2X4Y=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crash_platform
DB_USERNAME=crash_admin
DB_PASSWORD=SetYourPasswordHere!

SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=database

GITHUB_REPO_OWNER=faruk-spec
GITHUB_REPO_NAME=crash-platform
GITHUB_WEBHOOK_SECRET=SetYourWebhookSecretHere!
PREDICTOR_DEFAULT_CONFIDENCE=0.75
PREDICTOR_DEFAULT_MODE=BEFORE_5X
AUTO_SYNC_INTERVAL_SECONDS=30
EOF
    chown "$WEB_USER:$WEB_GROUP" "$BASE_DIR/shared/.env"
    chmod 600 "$BASE_DIR/shared/.env"
    echo "Created template .env at $BASE_DIR/shared/.env"
else
    echo "Existing .env found at $BASE_DIR/shared/.env, leaving intact."
fi

echo "=== [4/5] Checking PHP 8.3 & Extensions ==="
PHP_BIN=$(which php || which /www/server/php/83/bin/php || true)
if [ -n "$PHP_BIN" ]; then
    echo "Detected PHP at: $PHP_BIN ($($PHP_BIN -r 'echo PHP_VERSION;'))"
    REQUIRED_EXTS=("pdo_mysql" "mbstring" "bcmath" "curl")
    for ext in "${REQUIRED_EXTS[@]}"; do
        if $PHP_BIN -m | grep -qi "$ext"; then
            echo "  ✔ Extension $ext: INSTALLED"
        else
            echo "  ⚠ Extension $ext: MISSING (Install via aaPanel App Store -> PHP 8.3 -> Extensions)"
        fi
    done
else
    echo "⚠ PHP not detected in system path. If using aaPanel, add /www/server/php/83/bin to PATH."
fi

echo "=== [5/5] Provisioning Summary & Next Steps ==="
echo ""
echo "1. Set aaPanel Website DocumentRoot to:"
echo "   /www/wwwroot/crash-platform/current/public"
echo ""
echo "2. Create MySQL Database in aaPanel:"
echo "   Database Name: crash_platform"
echo "   Username:      crash_admin"
echo "   Update the password inside: $BASE_DIR/shared/.env"
echo ""
echo "3. Add your SSH Private Key to GitHub Secrets:"
echo "   Settings -> Secrets and variables -> Actions"
echo "   - VPS_HOST: $(curl -s ifconfig.me || echo 'YOUR_VPS_IP')"
echo "   - VPS_USERNAME: root"
echo "   - VPS_SSH_PRIVATE_KEY: (Contents of your id_rsa)"
echo ""
echo "Setup Complete! VPS is ready for automated GitHub Actions deployment."
