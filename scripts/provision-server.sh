#!/usr/bin/env bash
#
# Provisions a fresh Ubuntu LTS droplet as a Portal application server
# (doc 07 §6). Run once as root on a new droplet, then complete the manual
# steps printed at the end. Re-running is safe.
#
# This script is half of the cold-start deployment test: a clean server plus
# this script plus the documentation must yield a working Portal.

set -euo pipefail

DEPLOY_USER="deploy"
APP_DIR="/var/www/portal"
PHP_VERSION="8.3"

echo "== System packages =="
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get upgrade -y
apt-get install -y software-properties-common curl git unzip ufw fail2ban \
    unattended-upgrades nginx postgresql-client

add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y \
    "php${PHP_VERSION}-fpm" "php${PHP_VERSION}-cli" "php${PHP_VERSION}-pgsql" \
    "php${PHP_VERSION}-mbstring" "php${PHP_VERSION}-xml" "php${PHP_VERSION}-curl" \
    "php${PHP_VERSION}-zip" "php${PHP_VERSION}-intl" "php${PHP_VERSION}-gd" \
    "php${PHP_VERSION}-bcmath"

echo "== Deploy user =="
if ! id "$DEPLOY_USER" &>/dev/null; then
    adduser --disabled-password --gecos "" "$DEPLOY_USER"
fi
mkdir -p "/home/${DEPLOY_USER}/.ssh"
touch "/home/${DEPLOY_USER}/.ssh/authorized_keys"
chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "/home/${DEPLOY_USER}/.ssh"
chmod 700 "/home/${DEPLOY_USER}/.ssh"
chmod 600 "/home/${DEPLOY_USER}/.ssh/authorized_keys"

echo "== Application directory =="
mkdir -p "$APP_DIR"
chown -R "${DEPLOY_USER}:www-data" "$APP_DIR"

echo "== Firewall =="
ufw default deny incoming
ufw default allow outgoing
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "== SSH hardening =="
sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config
systemctl reload ssh

echo "== Unattended security upgrades =="
dpkg-reconfigure -f noninteractive unattended-upgrades

echo "== Queue worker service =="
cat > /etc/systemd/system/portal-queue.service <<UNIT
[Unit]
Description=Portal queue worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=3
ExecStart=/usr/bin/php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=${APP_DIR}

[Install]
WantedBy=multi-user.target
UNIT
systemctl daemon-reload
systemctl enable portal-queue

echo "== Nginx site =="
cat > /etc/nginx/sites-available/portal <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name _;
    root /var/www/portal/public;
    index index.php;

    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    client_max_body_size 10m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
NGINX
ln -sf /etc/nginx/sites-available/portal /etc/nginx/sites-enabled/portal
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

cat <<'NEXT'

Provisioning complete. Manual steps that remain (docs/DEPLOYMENT.md):

 1. Add the GitHub Actions deploy public key to /home/deploy/.ssh/authorized_keys.
 2. Create /var/www/portal/.env from .env.example with this environment's values
    (database, Postmark, Sentry, APP_KEY via `php artisan key:generate`).
 3. Install a Cloudflare origin certificate and switch the nginx site to TLS,
    or keep Cloudflare "Flexible" TLS during initial staging only.
 4. Point DNS at this droplet through Cloudflare (proxied).
 5. Run the first deploy from GitHub Actions, then: systemctl start portal-queue
 6. Create administrative accounts:
    php artisan portal:create-admin <email> <first> <last> [--founder]

NEXT
