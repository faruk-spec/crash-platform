#!/usr/bin/env bash
# ==============================================================================
# Autonomous Crash Platform - One-Click VPS Website Activator
# Solves: composer, vendor/autoload.php, permissions, .env, and key generation
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

# 1. Locate PHP 8.3
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

# 2. Setup .env file
cd "$SITE_ROOT/backend"
if [ ! -f ".env" ]; then
    cp .env.example .env
    sed -i "s|APP_URL=.*|APP_URL=https://playroot.in|g" .env
    echo "✔ Created backend/.env"
else
    echo "✔ Existing backend/.env found"
fi

# 3. Setup Storage Directories
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# 4. Install Composer & Vendor Dependencies
echo "=== Installing Backend Vendor Dependencies ==="
if [ ! -f "composer.phar" ] && ! which composer &>/dev/null; then
    echo "Downloading self-contained composer.phar..."
    curl -sS https://getcomposer.org/installer | "$PHP_BIN"
fi

if [ -f "composer.phar" ]; then
    "$PHP_BIN" composer.phar install --no-dev --optimize-autoloader --no-interaction
elif which composer &>/dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "Error: Failed to execute Composer."
    exit 1
fi

# 5. Generate Application Key
echo "=== Generating Application Key ==="
"$PHP_BIN" artisan key:generate --force

# 6. Optimize Caches
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear

# 7. Configure Web Ownership & Permissions
echo "=== Setting Web Permissions ==="
if id "www" &>/dev/null; then
    WEB_USER="www"
    WEB_GROUP="www"
else
    WEB_USER="www-data"
    WEB_GROUP="www-data"
fi

chown -R "$WEB_USER:$WEB_GROUP" "$SITE_ROOT"
chmod -R 775 "$SITE_ROOT/backend/storage" "$SITE_ROOT/backend/bootstrap/cache"

# 8. Verification
if [ -f "$SITE_ROOT/backend/vendor/autoload.php" ]; then
    echo ""
    echo "=========================================================="
    echo " SUCCESS: Backend Vendor Dependencies Ready!"
    echo " Website is now LIVE at: https://playroot.in"
    echo "=========================================================="
    echo ""
    echo "Next: If you have created your database in aaPanel:"
    echo "  1. Put your DB password into: $SITE_ROOT/backend/.env"
    echo "  2. Run migrations: cd $SITE_ROOT/backend && $PHP_BIN artisan migrate --force"
    echo ""
else
    echo "Warning: vendor/autoload.php still missing. Check composer output above."
fi
