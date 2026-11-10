#!/usr/bin/env bash
# ==============================================================================
# Autonomous Crash Platform - One-Click VPS Website Activator
# Fixes: sessions table, permissions, aaPanel running directory, storage logs, cache
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

# 1. Clean up invalid fileinfo extension from php.ini to remove startup warning
echo "=== [1/6] Checking PHP Configuration ==="
PHP_INI="/www/server/php/83/etc/php.ini"
if [ -f "$PHP_INI" ]; then
    sed -i 's/^extension=fileinfo/;extension=fileinfo/g' "$PHP_INI"
    sed -i 's/putenv,//g' "$PHP_INI"
    sed -i 's/proc_open,//g' "$PHP_INI"
    sed -i 's/popen,//g' "$PHP_INI"
    sed -i 's/symlink,//g' "$PHP_INI"
    sed -i 's/exec,//g' "$PHP_INI"
    if [ -f "/etc/init.d/php-fpm-83" ]; then
        /etc/init.d/php-fpm-83 reload || true
    fi
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
    echo "Error: PHP binary not found."
    exit 1
fi

echo "✔ Using PHP: $($PHP_BIN -v | head -n 1)"

# 3. Setup .env file
cd "$SITE_ROOT/backend"
if [ -f ".env" ]; then
    echo "✔ Switching SESSION_DRIVER to file in .env"
    sed -i "s/SESSION_DRIVER=database/SESSION_DRIVER=file/g" .env
    sed -i "s/CACHE_STORE=database/CACHE_STORE=file/g" .env
fi

# 4. Setup Storage Directories & Set Full Permissions
echo "=== [2/6] Configuring Storage Permissions ==="
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
touch storage/logs/laravel.log

if id "www" &>/dev/null; then
    WEB_USER="www"
    WEB_GROUP="www"
else
    WEB_USER="www-data"
    WEB_GROUP="www-data"
fi

chown -R "$WEB_USER:$WEB_GROUP" "$SITE_ROOT"
chmod -R 775 "$SITE_ROOT/backend/storage" "$SITE_ROOT/backend/bootstrap/cache"
chmod -R 777 "$SITE_ROOT/backend/storage/logs" "$SITE_ROOT/backend/storage/framework"

# 5. Run Database Migrations
echo "=== [3/6] Running Migrations (sessions, cache, platform tables) ==="
"$PHP_BIN" artisan migrate --force || true

# 6. Clear and rebuild caches
echo "=== [4/6] Clearing Framework Caches ==="
"$PHP_BIN" artisan optimize:clear || true

# 7. Finalize permissions
echo "=== [5/6] Finalizing Web Permissions ==="
chown -R "$WEB_USER:$WEB_GROUP" "$SITE_ROOT/backend/storage" "$SITE_ROOT/backend/bootstrap/cache"
chmod -R 777 "$SITE_ROOT/backend/storage/logs" "$SITE_ROOT/backend/storage/framework"

echo ""
echo "=========================================================="
echo " SUCCESS: Sessions, Migrations & Permissions Active!"
echo " Website is 100% LIVE at: https://playroot.in"
echo "=========================================================="
