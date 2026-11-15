# Déploiement en production

## Serveur requis

- PHP 8.4.1 ou plus (FPM) avec `intl`, `pdo_mysql`, `gd` (WebP), `mbstring`, `fileinfo`, `zip`, `opcache`.
- MariaDB 10.6 ou plus (InnoDB, utf8mb4).
- Nginx ou Apache avec la racine web sur le dossier `public/`.
- Composer 2, et Node.js 20 ou plus (uniquement pour compiler les assets, en CI ou sur le serveur).
- Un accès SMTP pour les notifications (Brevo, Mailjet, OVH…).
- HTTPS obligatoire (Let's Encrypt).

## 1. Base de données

```sql
CREATE DATABASE solution_netmobile CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'netmobile'@'localhost' IDENTIFIED BY 'mot-de-passe-fort';
GRANT ALL PRIVILEGES ON solution_netmobile.* TO 'netmobile'@'localhost';
```

## 2. Fichier `.env` de production

```dotenv
APP_NAME="Solution Netmobile"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.solution-netmobile.fr

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=solution_netmobile
DB_USERNAME=netmobile
DB_PASSWORD=mot-de-passe-fort

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database        # ou redis
FILESYSTEM_DISK=public
LOG_LEVEL=warning

MAIL_MAILER=smtp
MAIL_HOST=smtp.exemple.fr
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="no-reply@solution-netmobile.fr"
MAIL_FROM_NAME="Solution Netmobile"

ADMIN_EMAIL=florent@solution-netmobile.fr
ADMIN_PASSWORD=             # vide : un mot de passe est généré et affiché une seule fois
CONTACT_RETENTION_MONTHS=36
TRUSTED_PROXIES=            # "*" derrière un load balancer qui termine le HTTPS
```

En production :

- `robots.txt` autorise l'indexation, `/admin` excepté ;
- la balise `noindex` est retirée ;
- les URLs sont forcées en HTTPS.

## 3. Première installation

```bash
git clone <dépôt> /var/www/solution-netmobile && cd /var/www/solution-netmobile
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env    # puis éditer
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force          # rôles, admin, contenus de la maquette (sans prospects de démo)
php artisan storage:link
php artisan optimize                 # config, routes, vues, événements en cache
php artisan filament:optimize        # composants et icônes Filament en cache
```

Droits d'écriture pour l'utilisateur web : `storage/` et `bootstrap/cache/`.

## 4. Tâches planifiées

La purge RGPD (`contacts:purge`) est planifiée chaque nuit. Ajoutez une seule entrée cron :

```cron
* * * * * cd /var/www/solution-netmobile && php artisan schedule:run >> /dev/null 2>&1
```

## 5. Exemple Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name www.solution-netmobile.fr;
    root /var/www/solution-netmobile/public;
    index index.php;

    client_max_body_size 8M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Aucun script ne doit être exécuté depuis les médias téléversés
    location ^~ /storage/ {
        location ~ \.php$ { deny all; }
        add_header X-Content-Type-Options nosniff;
        add_header Content-Security-Policy "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:" always;
        expires 30d;
    }

    location ~* \.(css|js|woff2|svg|png|webp|jpg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri /index.php?$query_string;
    }

    location ~ /\.(?!well-known) { deny all; }
}
```

## 6. Mises à jour

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force   # si de nouvelles permissions ont été ajoutées
php artisan optimize && php artisan filament:optimize
php artisan up
```

## 7. Sauvegardes

- Base : `mariadb-dump --single-transaction solution_netmobile | gzip > backup-$(date +%F).sql.gz` (quotidien, avec rotation).
- Fichiers : `storage/app/public` (logos, images, photo du dirigeant).

## 8. Avant la mise en ligne

1. Se connecter à `/admin` et changer le mot de passe du super administrateur (menu *Profil*).
2. *Paramètres du site* : remplir SIRET, forme juridique, siège, destinataires des notifications, URL LinkedIn ; téléverser logo, favicon et image de partage.
3. *Clients & références* : remplacer les 4 logos de démonstration.
4. *Pages* : compléter l'hébergeur dans les Mentions légales, relire la Politique de confidentialité.
5. Envoyer une demande test depuis le formulaire et vérifier la réception de l'e-mail.
6. Déclarer `https://www.solution-netmobile.fr/sitemap.xml` dans Google Search Console.
