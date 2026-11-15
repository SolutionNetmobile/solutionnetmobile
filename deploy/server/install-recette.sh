#!/usr/bin/env bash
# =============================================================================
# Installation (une seule fois) de la recette Solution Netmobile sur le serveur
# qui héberge déjà Private VTC. Idempotent : peut être relancé sans risque.
#
#   sudo bash deploy/server/install-recette.sh
#
# Ne modifie AUCUN réglage de Private VTC : utilisateur, pool PHP-FPM, base,
# vhost Nginx, certificat et clé SSH sont tous dédiés à Solution Netmobile.
# =============================================================================
set -Eeuo pipefail

DOMAIN="recette.solution-netmobile.fr"
ENV_NAME="recette"
PHP_VERSION="${NETMOBILE_PHP_VERSION:-8.5}"
APP_USER="netmobile-${ENV_NAME}"
BASE="/var/www/netmobile/${ENV_NAME}"
DB_NAME="solution_netmobile_${ENV_NAME}"
DB_USER="netmobile_${ENV_NAME}"
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${HERE}/../.." && pwd)"

log() { printf '\n==> %s\n' "$*"; }
[ "$(id -u)" -eq 0 ] || { echo "À lancer en root (sudo)." >&2; exit 1; }
for bin in nginx "php${PHP_VERSION}" mariadb composer npm git certbot htpasswd; do
  command -v "$bin" >/dev/null || { echo "Commande manquante : $bin" >&2; exit 1; }
done
id deploy >/dev/null 2>&1 || { echo "L'utilisateur « deploy » (Private VTC) est attendu." >&2; exit 1; }

log "Utilisateur système ${APP_USER}"
id "$APP_USER" >/dev/null 2>&1 || useradd --system --home-dir "$BASE" --shell /usr/sbin/nologin "$APP_USER"
usermod -aG "$APP_USER" www-data
install -d -o "$APP_USER" -g www-data -m 750 /var/www/netmobile "$BASE" "$BASE/shared" "$BASE/releases" "$BASE/backups"

log "Base MariaDB ${DB_NAME}"
CREDENTIALS="/root/.netmobile-${ENV_NAME}-db"
if [ ! -f "$CREDENTIALS" ]; then
  umask 077
  echo "$(openssl rand -base64 30 | tr -dc 'A-Za-z0-9' | head -c 32)" > "$CREDENTIALS"
fi
DB_PASSWORD="$(cat "$CREDENTIALS")"
mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

log "Fichier .env partagé"
ENV_FILE="$BASE/shared/.env"
if [ ! -f "$ENV_FILE" ]; then
  ADMIN_PASSWORD="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 16)Aa1!"
  cp "$REPO_ROOT/.env.example" "$ENV_FILE"
  sed -i \
    -e "s|^APP_ENV=.*|APP_ENV=staging|" \
    -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
    -e "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" \
    -e "s|^DB_PORT=.*|DB_PORT=3306|" \
    -e "s|^DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" \
    -e "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" \
    -e "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" \
    -e "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" \
    -e "s|^LOG_STACK=.*|LOG_STACK=daily|" \
    -e "s|^LOG_LEVEL=.*|LOG_LEVEL=info|" \
    -e "s|^ADMIN_PASSWORD=.*|ADMIN_PASSWORD=${ADMIN_PASSWORD}|" \
    -e "s|^CACHE_PREFIX=.*|CACHE_PREFIX=netmobile_recette|" \
    "$ENV_FILE"
  grep -q '^CACHE_PREFIX=' "$ENV_FILE" || echo "CACHE_PREFIX=netmobile_recette" >> "$ENV_FILE"
  echo "Compte super administrateur de la recette : admin@solution-netmobile.fr / ${ADMIN_PASSWORD}"
  echo "(à changer après la première connexion ; les réglages SMTP MAIL_* restent à renseigner dans ${ENV_FILE})"
fi
chown "$APP_USER:$APP_USER" "$ENV_FILE"
chmod 640 "$ENV_FILE"

log "Pool PHP-FPM ${PHP_VERSION}"
install -m 644 "$HERE/php-fpm-recette.conf" "/etc/php/${PHP_VERSION}/fpm/pool.d/netmobile-recette.conf"
sed -i "s|/etc/php/[0-9.]*/|/etc/php/${PHP_VERSION}/|" "/etc/php/${PHP_VERSION}/fpm/pool.d/netmobile-recette.conf"
"php-fpm${PHP_VERSION}" -t
systemctl reload "php${PHP_VERSION}-fpm"

log "Scripts de déploiement"
install -m 755 "$HERE/netmobile-deploy" /usr/local/bin/netmobile-deploy
install -m 755 "$HERE/netmobile-ssh-gate" /usr/local/bin/netmobile-ssh-gate
cat > /etc/sudoers.d/netmobile-deploy <<'SUDO'
deploy ALL=(root) NOPASSWD: /usr/local/bin/netmobile-deploy recette *
SUDO
chmod 440 /etc/sudoers.d/netmobile-deploy
visudo -cf /etc/sudoers.d/netmobile-deploy

log "Mot de passe HTTP de la recette"
install -d -m 755 /etc/nginx/htpasswd
HTPASSWD=/etc/nginx/htpasswd/netmobile-recette
if [ ! -f "$HTPASSWD" ]; then
  RECETTE_PASSWORD="$(openssl rand -base64 18 | tr -dc 'A-Za-z0-9' | head -c 14)"
  htpasswd -cbB "$HTPASSWD" recette "$RECETTE_PASSWORD"
  echo "Accès HTTP recette : recette / ${RECETTE_PASSWORD}"
fi
chown root:www-data "$HTPASSWD"; chmod 640 "$HTPASSWD"

log "Certificat Let's Encrypt"
install -d -m 755 /var/www/letsencrypt
if [ ! -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
  # Vhost HTTP minimal le temps d'obtenir le certificat
  cat > /etc/nginx/sites-available/netmobile-recette.conf <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    location /.well-known/acme-challenge/ { root /var/www/letsencrypt; }
    location / { return 404; }
}
NGINX
  ln -sfn /etc/nginx/sites-available/netmobile-recette.conf /etc/nginx/sites-enabled/netmobile-recette.conf
  nginx -t && systemctl reload nginx
  certbot certonly --webroot -w /var/www/letsencrypt -d "$DOMAIN" --non-interactive --agree-tos \
    --register-unsafely-without-email --keep-until-expiring
fi

log "Vhost Nginx"
install -m 644 "$HERE/nginx-recette.conf" /etc/nginx/sites-available/netmobile-recette.conf
ln -sfn /etc/nginx/sites-available/netmobile-recette.conf /etc/nginx/sites-enabled/netmobile-recette.conf
nginx -t
systemctl reload nginx

log "Tâches planifiées (purge RGPD nocturne)"
cat > /etc/cron.d/netmobile-recette <<CRON
* * * * * ${APP_USER} [ -L ${BASE}/current ] && cd ${BASE}/current && /usr/bin/php${PHP_VERSION} artisan schedule:run >> /dev/null 2>&1
CRON
chmod 644 /etc/cron.d/netmobile-recette

log "Clé SSH pour GitHub Actions (bridée sur le déploiement de la recette)"
KEYS_DIR=/home/deploy/gha-keys
install -d -o deploy -g deploy -m 700 "$KEYS_DIR"
KEY="$KEYS_DIR/gha_netmobile_recette"
if [ ! -f "$KEY" ]; then
  sudo -u deploy ssh-keygen -q -t ed25519 -N '' -C gha_netmobile_recette -f "$KEY"
fi
AUTH=/home/deploy/.ssh/authorized_keys
install -d -o deploy -g deploy -m 700 /home/deploy/.ssh
touch "$AUTH"; chown deploy:deploy "$AUTH"; chmod 600 "$AUTH"
if ! grep -q 'gha_netmobile_recette' "$AUTH"; then
  echo "restrict,command=\"/usr/local/bin/netmobile-ssh-gate recette\" $(cat "$KEY.pub")" >> "$AUTH"
fi

cat <<DONE

==============================================================================
Installation terminée. À faire dans GitHub (environnement « recette ») :

  SSH_HOST         : $(hostname -I | awk '{print $1}')
  SSH_KNOWN_HOSTS  : $(ssh-keyscan -t ed25519 127.0.0.1 2>/dev/null | sed "s/^127.0.0.1/$(hostname -I | awk '{print $1}')/")
  SSH_PRIVATE_KEY  : contenu de ${KEY}   (puis : sudo rm ${KEY})
  HEALTH_BASIC_AUTH: recette:<mot de passe HTTP affiché ci-dessus>

Puis variable de dépôt DEPLOY_RECETTE_ENABLED = true, et premier déploiement :
  sudo -u deploy sudo /usr/local/bin/netmobile-deploy recette deploy
==============================================================================
DONE
