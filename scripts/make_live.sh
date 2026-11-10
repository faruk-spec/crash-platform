#!/usr/bin/env bash
# ==============================================================================
# Autonomous Crash Platform - One-Click VPS Website Activator
# Solves: aaPanel disable_functions (putenv, proc_open), composer, permissions
# ==============================================================================

set -e

# Detect site root directory
if [ -d "backend" ]; then
    SITE_ROOT=$(pwd)
elif [ -d "/www/wwwroot/playroot.in/backend" ]; then
    SITE_ROOT="/www/wwwroot/playroot.in"
else
    echo "Error: Cannot find backend/ directory. Please run this inside your site folder."
    exit 1
fi

echo "=========================================================="
echo "Activating Crash Game Platform at: $SITE_ROOT"
echo "=========================================================="

cd "$SITE_ROOT"

# Ensure Git safe directory
git config --global --add safe.directory "$SITE_ROOT" 2>/dev/null || true

# 1. Automatically unblock required Composer functions in aaPanel php.ini
echo "=== [1/6] Checking aaPanel PHP Disabled Functions ==="
PHP_INI_FILES=(
    "/www/server/php/83/etc/php.ini"
    "/etc/php/8.3/cli/php.ini"
    "/etc/php/8.3/fpm/php.ini"
)

for ini in "${PHP_INI_FILES[@]}"; do
    if [ -f "$ini" ]; then
        echo "Unblocking putenv, proc_open, popen, symlink, exec in $ini"
        sed -i 's/putenv,//g' "$ini"
        sed -i 's/proc_open,//g' "$ini"
        sed -i 's/popen,//g' "$ini"
        sed -i 's/symlink,//g' "$ini"
        sed -i 's/exec,//g' "$ini"
    fi
done

if [ -f "/etc/init.d/php-fpm-83" ]; then
    /etc/init.d/php-fpm-83 reload || true
elif systemctl is-active --quiet php-fpm-83 2>/dev/null; then
    systemctl reload php-fpm-83 || true
fi

# 2. Locate PHP 8.3 binary
PHP_BIN=""
CANDIDATES=(
    "/www/server/php/83/bin/php"
    "/usr/bin/php8.3"
    "$(which php 2>/dev/null)"
)

for p in "${CANDIDATES[@]}"; do
    if [ -n "$p" ] && [ -x "$p" ]; then
        PHP_BIN="$p"
        break
    fi
done

if [ -z "$PHP_BIN" ]; then
    echo "Error: PHP binary not found. Please install PHP 8.3 in aaPanel."
    exit 1
fi

echo "✔ Using PHP: $($PHP_BIN -v | head -n 1)"

# 3. Setup .env file
cd "$SITE_ROOT/backend"
if [ ! -f ".env" ]; then
    cp .env.example .env
    sed -i "s|APP_URL=.*|APP_URL=https://playroot.in|g" .env
    echo "✔ Created backend/.env"
else
    echo "✔ Existing backend/.env found"
fi

# 4. Setup Storage Directories
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# 5. Install Composer & Vendor Dependencies
echo "=== [2/6] Installing Backend Vendor Dependencies ==="
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_AUDIT_BLOCK=0

if [ ! -f "composer.phar" ]; then
    echo "Downloading self-contained composer.phar..."
    curl -sS https://getcomposer.org/installer | "$PHP_BIN"
fi

# Disable advisory blocking in composer
"$PHP_BIN" composer.phar config policy.advisories.block false 2>/dev/null || true
"$PHP_BIN" composer.phar config audit.block false 2>/dev/null || true

echo "Running composer update to build vendor and lock file..."
"$PHP_BIN" -d memory_limit=1024M composer.phar update --no-dev --optimize-autoloader --no-interaction --no-audit

# 6. Generate Application Key
echo "=== [3/6] Generating Application Key ==="
"$PHP_BIN" artisan key:generate --force

# 7. Optimize Caches
echo "=== [4/6] Clearing Framework Caches ==="
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear

# 8. Configure Web Ownership & Permissions
echo "=== [5/6] Setting Web Permissions ==="
if id "www" &>/dev/null; then
    WEB_USER="www"
    WEB_GROUP="www"
else
    WEB_USER="www-data"
    WEB_GROUP="www-data"
fi

chown -R "$WEB_USER:$WEB_GROUP" "$SITE_ROOT"
chmod -R 775 "$SITE_ROOT/backend/storage" "$SITE_ROOT/backend/bootstrap/cache"

# 9. Verification
echo "=== [6/6] Verifying System State ==="
if [ -f "$SITE_ROOT/backend/vendor/autoload.php" ]; then
    echo ""
    echo "=========================================================="
    echo " SUCCESS: Backend Vendor Dependencies Ready!"
    echo " Website is now LIVE at: https://playroot.in"
    echo "=========================================================="
    echo ""
    echo "Next: In aaPanel -> Database, create crash_platform database,"
    echo "then run: cd $SITE_ROOT/backend && $PHP_BIN artisan migrate --force"
    echo ""
else
    echo "Warning: vendor/autoload.php still missing. Check output above."
fi
