#!/usr/bin/env bash
# ==============================================================================
# Helper Script: Configure aaPanel Cloned Site (/www/wwwroot/playroot.in)
# ==============================================================================

set -e

# Detect site directory
SITE_DIR=$(pwd)
if [ ! -d "$SITE_DIR/backend" ]; then
    if [ -d "/www/wwwroot/playroot.in/backend" ]; then
        SITE_DIR="/www/wwwroot/playroot.in"
    else
        echo "Error: Could not locate backend/ directory. Please cd into your site directory first."
        exit 1
    fi
fi

cd "$SITE_DIR/backend"
echo "=== Configuring Backend at $SITE_DIR/backend ==="

# 1. Setup .env
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "Created .env from .env.example"
    fi
fi

# 2. Setup storage directories and permissions
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Determine web user
if id "www" &>/dev/null; then
    WEB_USER="www"
    WEB_GROUP="www"
else
    WEB_USER="www-data"
    WEB_GROUP="www-data"
fi

chown -R "$WEB_USER:$WEB_GROUP" "$SITE_DIR"
chmod -R 775 storage bootstrap/cache

# 3. Locate Composer and PHP 8.3
PHP_BIN=$(which /www/server/php/83/bin/php || which php || true)
COMPOSER_BIN=$(which composer || which /usr/local/bin/composer || true)

if [ -z "$PHP_BIN" ]; then
    echo "Error: PHP binary not found. Please install PHP 8.3 in aaPanel."
    exit 1
fi

echo "Using PHP: $($PHP_BIN -v | head -n 1)"

if [ -n "$COMPOSER_BIN" ]; then
    echo "Running Composer Install..."
    $PHP_BIN "$COMPOSER_BIN" install --no-dev --optimize-autoloader || composer install --no-dev --optimize-autoloader
else
    echo "Composer not found globally. Running via aaPanel PHP composer..."
    composer install --no-dev --optimize-autoloader || echo "Please run Composer Install via aaPanel -> Composer menu"
fi

# 4. Generate App Key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    $PHP_BIN artisan key:generate --force
fi

# 5. Fix permissions again
chown -R "$WEB_USER:$WEB_GROUP" storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo ""
echo "=== Configuration Complete! ==="
echo "1. In aaPanel -> Site Modification [playroot.in] -> Directory:"
echo "   Set 'Running directory' to: /backend/public (or leave as / with the root .htaccess)"
echo "2. In aaPanel -> URL rewrite:"
echo "   Select 'Laravel' preset and click Save."
echo "3. Update your DB_PASSWORD in /www/wwwroot/playroot.in/backend/.env"
echo "4. Run database migrations: cd /www/wwwroot/playroot.in/backend && php artisan migrate --force"

