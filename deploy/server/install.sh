#!/usr/bin/env bash
# =============================================================================
# Installation (une seule fois par environnement) de Solution Netmobile sur le
# serveur qui héberge déjà Private VTC. Idempotent : peut être relancé sans risque.
#
#   sudo bash deploy/server/install.sh recette   # https://recette.solution-netmobile.fr
#   sudo bash deploy/server/install.sh prod      # https://solution-netmobile.fr
#
# Ne modifie AUCUN réglage de Private VTC : utilisateur, pool PHP-FPM, base,
# vhost Nginx, certificat et clé SSH sont tous dédiés à chaque environnement.
# =============================================================================
set -Eeuo pipefail

ENV_NAME="${1:-}"
case "$ENV_NAME" in
  recette)
    DOMAIN="recette.solution-netmobile.fr"
    EXTRA_DOMAINS=""
    APP_ENV_VALUE="staging"
    LOG_LEVEL_VALUE="info"
    HTTP_PASSWORD="yes"
    ;;
  prod)
    DOMAIN="solution-netmobile.fr"
    EXTRA_DOMAINS="www.solution-netmobile.fr"
    APP_ENV_VALUE="production"
    LOG_LEVEL_VALUE="warning"
    HTTP_PASSWORD="no"
    ;;
  *)
    echo "Usage : sudo bash $0 <recette|prod>" >&2
    exit 2
    ;;
esac

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
install -d -m 755 /var/www/netmobile
install -d -o "$APP_USER" -g www-data -m 750 "$BASE" "$BASE/shared" "$BASE/releases" "$BASE/backups"

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
    -e "s|^APP_ENV=.*|APP_ENV=${APP_ENV_VALUE}|" \
    -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
    -e "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" \
    -e "s|^DB_PORT=.*|DB_PORT=3306|" \
    -e "s|^DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" \
    -e "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" \
    -e "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" \
    -e "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" \
    -e "s|^LOG_STACK=.*|LOG_STACK=daily|" \
    -e "s|^LOG_LEVEL=.*|LOG_LEVEL=${LOG_LEVEL_VALUE}|" \
    -e "s|^ADMIN_PASSWORD=.*|ADMIN_PASSWORD=${ADMIN_PASSWORD}|" \
    -e "s|^CACHE_PREFIX=.*|CACHE_PREFIX=netmobile_${ENV_NAME}|" \
    "$ENV_FILE"
  grep -q '^CACHE_PREFIX=' "$ENV_FILE" || echo "CACHE_PREFIX=netmobile_${ENV_NAME}" >> "$ENV_FILE"
  echo "Compte super administrateur (${ENV_NAME}) : admin@solution-netmobile.fr / ${ADMIN_PASSWORD}"
  echo "(à changer après la première connexion ; les réglages SMTP MAIL_* restent à renseigner dans ${ENV_FILE})"
fi
chown "$APP_USER:$APP_USER" "$ENV_FILE"
chmod 640 "$ENV_FILE"

log "Pool PHP-FPM ${PHP_VERSION}"
POOL="/etc/php/${PHP_VERSION}/fpm/pool.d/netmobile-${ENV_NAME}.conf"
install -m 644 "$HERE/php-fpm-${ENV_NAME}.conf" "$POOL"
sed -i "s|/etc/php/[0-9.]*/|/etc/php/${PHP_VERSION}/|" "$POOL"
"php-fpm${PHP_VERSION}" -t
systemctl reload "php${PHP_VERSION}-fpm"

log "Scripts de déploiement"
install -m 755 "$HERE/netmobile-deploy" /usr/local/bin/netmobile-deploy
install -m 755 "$HERE/netmobile-ssh-gate" /usr/local/bin/netmobile-ssh-gate
cat > /etc/sudoers.d/netmobile-deploy <<'SUDO'
deploy ALL=(root) NOPASSWD: /usr/local/bin/netmobile-deploy recette *, /usr/local/bin/netmobile-deploy prod *
SUDO
chmod 440 /etc/sudoers.d/netmobile-deploy
visudo -cf /etc/sudoers.d/netmobile-deploy

if [ "$HTTP_PASSWORD" = "yes" ]; then
  log "Mot de passe HTTP (${ENV_NAME})"
  install -d -m 755 /etc/nginx/htpasswd
  HTPASSWD="/etc/nginx/htpasswd/netmobile-${ENV_NAME}"
  if [ ! -f "$HTPASSWD" ]; then
    HTTP_SECRET="$(openssl rand -base64 18 | tr -dc 'A-Za-z0-9' | head -c 14)"
    htpasswd -cbB "$HTPASSWD" "$ENV_NAME" "$HTTP_SECRET"
    echo "Accès HTTP ${ENV_NAME} : ${ENV_NAME} / ${HTTP_SECRET}"
  fi
  chown root:www-data "$HTPASSWD"; chmod 640 "$HTPASSWD"
fi

log "Certificat Let's Encrypt"
VHOST="/etc/nginx/sites-available/netmobile-${ENV_NAME}.conf"
install -d -m 755 /var/www/letsencrypt
CERT_DOMAINS=(-d "$DOMAIN")
for extra in $EXTRA_DOMAINS; do
  if getent hosts "$extra" >/dev/null; then
    CERT_DOMAINS+=(-d "$extra")
  else
    echo "Attention : ${extra} ne résout pas (DNS), il n'est pas ajouté au certificat."
  fi
done
if [ ! -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
  # Vhost HTTP minimal le temps d'obtenir le certificat
  cat > "$VHOST" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} ${EXTRA_DOMAINS};
    location /.well-known/acme-challenge/ { root /var/www/letsencrypt; }
    location / { return 404; }
}
NGINX
  ln -sfn "$VHOST" "/etc/nginx/sites-enabled/netmobile-${ENV_NAME}.conf"
  nginx -t && systemctl reload nginx
fi
# --expand ajoute un domaine (ex. www) à un certificat existant ; sans effet sinon
certbot certonly --webroot -w /var/www/letsencrypt --cert-name "$DOMAIN" "${CERT_DOMAINS[@]}" \
  --non-interactive --agree-tos --register-unsafely-without-email --keep-until-expiring --expand

log "Vhost Nginx"
install -m 644 "$HERE/nginx-${ENV_NAME}.conf" "$VHOST"
ln -sfn "$VHOST" "/etc/nginx/sites-enabled/netmobile-${ENV_NAME}.conf"
nginx -t
systemctl reload nginx

log "Tâches planifiées (purge RGPD nocturne)"
cat > "/etc/cron.d/netmobile-${ENV_NAME}" <<CRON
* * * * * ${APP_USER} [ -L ${BASE}/current ] && cd ${BASE}/current && /usr/bin/php${PHP_VERSION} artisan schedule:run >> /dev/null 2>&1
CRON
chmod 644 "/etc/cron.d/netmobile-${ENV_NAME}"

log "Clé SSH pour GitHub Actions (bridée sur le déploiement ${ENV_NAME})"
KEYS_DIR=/home/deploy/gha-keys
install -d -o deploy -g deploy -m 700 "$KEYS_DIR"
KEY_NAME="gha_netmobile_${ENV_NAME}"
KEY="$KEYS_DIR/${KEY_NAME}"
AUTH=/home/deploy/.ssh/authorized_keys
install -d -o deploy -g deploy -m 700 /home/deploy/.ssh
touch "$AUTH"; chown deploy:deploy "$AUTH"; chmod 600 "$AUTH"
if ! grep -q "${KEY_NAME}\$" "$AUTH"; then
  [ -f "$KEY" ] || sudo -u deploy ssh-keygen -q -t ed25519 -N '' -C "$KEY_NAME" -f "$KEY"
  echo "restrict,command=\"/usr/local/bin/netmobile-ssh-gate ${ENV_NAME}\" $(cat "$KEY.pub")" >> "$AUTH"
fi

HEALTH_HINT=""
[ "$HTTP_PASSWORD" = "yes" ] && HEALTH_HINT="  HEALTH_BASIC_AUTH: ${ENV_NAME}:<mot de passe HTTP affiché ci-dessus>"
VARIABLE="DEPLOY_$(echo "$ENV_NAME" | tr '[:lower:]' '[:upper:]')_ENABLED"

cat <<DONE

==============================================================================
Installation terminée. À faire dans GitHub (environnement « ${ENV_NAME} ») :

  SSH_HOST         : $(hostname -I | awk '{print $1}')
  SSH_KNOWN_HOSTS  : $(ssh-keyscan -t ed25519 127.0.0.1 2>/dev/null | sed "s/^127.0.0.1/$(hostname -I | awk '{print $1}')/")
  SSH_PRIVATE_KEY  : contenu de ${KEY}   (puis : sudo rm ${KEY})
${HEALTH_HINT}

Puis variable de dépôt ${VARIABLE} = true, et premier déploiement :
  sudo -u deploy sudo /usr/local/bin/netmobile-deploy ${ENV_NAME} deploy
==============================================================================
DONE
