# VPS Deployment Guide (aaPanel, Apache & PHP 8.3)

## Server Requirements
* **OS**: Ubuntu 22.04 LTS or 24.04 LTS
* **Control Panel**: aaPanel (Standard installation)
* **Web Server**: Apache 2.4
* **PHP**: PHP 8.3+ with extensions: `pdo_mysql`, `mbstring`, `bcmath`, `curl`, `redis`, `gd`
* **Database**: MySQL 8.0+

## aaPanel Setup Steps

### 1. Directory Setup
```bash
mkdir -p /www/wwwroot/crash-platform/releases
mkdir -p /www/wwwroot/crash-platform/shared/storage
touch /www/wwwroot/crash-platform/shared/.env
```

### 2. Apache VirtualHost Configuration
In aaPanel Website settings, set the site DocumentRoot to:
```
/www/wwwroot/crash-platform/current/public
```

Ensure Apache `.htaccess` rewriting is enabled:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 3. GitHub Secrets Configuration
Set the following secrets in your GitHub repository (`Settings -> Secrets and variables -> Actions`):
* `VPS_HOST`: Server IP address or domain
* `VPS_USERNAME`: SSH deployment user (e.g. `root` or `www`)
* `VPS_SSH_PRIVATE_KEY`: Private SSH key
* `ANDROID_KEYSTORE_BASE64`: Base64 encoded release keystore
* `KEYSTORE_PASSWORD`: Keystore password
* `KEY_ALIAS`: Keystore key alias
* `KEY_PASSWORD`: Keystore alias password
* `BACKEND_RELEASE_WEBHOOK_URL`: `https://your-domain.com/api/v1/github/build-complete`
* `WEBHOOK_SECRET`: Secure secret string matching `GITHUB_WEBHOOK_SECRET` in `.env`
