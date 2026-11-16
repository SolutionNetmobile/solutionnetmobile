# Déploiement

## Recette — https://recette.solution-netmobile.fr

Même organisation que Private VTC, sur le même serveur. Toutes les ressources sont dédiées :
aucun réglage de Private VTC n'est modifié.

| Élément | Valeur |
|---|---|
| Branche | `recette` |
| Répertoire | `/var/www/netmobile/recette` (`releases/`, `shared/.env`, `shared/storage`, `backups/`, lien `current`) |
| Utilisateur système / pool PHP-FPM | `netmobile-recette` (`/run/php/netmobile-recette.sock`) |
| Base MariaDB | `solution_netmobile_recette` (utilisateur `netmobile_recette`) |
| Accès | mot de passe HTTP (`/etc/nginx/htpasswd/netmobile-recette`), `noindex`, `APP_ENV=staging` |
| Scripts | `/usr/local/bin/netmobile-deploy`, `/usr/local/bin/netmobile-ssh-gate` (sources : `deploy/server/`) |

### Principe

Un push sur `recette` lance `.github/workflows/deploy-recette.yml` :

1. Les tests sont lancés (`ci.yml` : Pint, PHPUnit sur MariaDB, build Vite).
2. GitHub se connecte en SSH avec `deploy@serveur "deploy <sha>"`. La clé est bridée par `netmobile-ssh-gate recette`.
3. Le serveur exécute `netmobile-deploy` :
   - verrou et récupération de la branche `recette` depuis GitHub ;
   - création d'une nouvelle release ;
   - `composer install --no-dev`, puis `npm ci && npm run build` ;
   - sauvegarde de la base, migrations, données initiales au premier déploiement ;
   - mise en cache (`optimize`, `filament:optimize`) ;
   - **contrôle de santé de la nouvelle release avant sa mise en ligne** ;
   - bascule atomique de `current` ;
   - contrôle public, avec **retour automatique** à la version précédente en cas d'échec ;
   - conservation des 5 dernières releases.
4. Contrôle public : `/` doit répondre 401 sans mot de passe, et `/up` 200 avec le mot de passe.

### Installation sur le serveur (une seule fois)

```bash
git clone --branch recette https://github.com/SolutionNetmobile/solutionnetmobile.git /tmp/netmobile
sudo bash /tmp/netmobile/deploy/server/install.sh recette
```

Le script :

1. crée l'utilisateur, la base, le fichier `.env` partagé, le pool PHP-FPM, le mot de passe HTTP, le certificat Let's Encrypt, le vhost Nginx, la tâche cron et la clé SSH bridée ;
2. vérifie `nginx -t` et `php-fpm -t` avant chaque rechargement ;
3. affiche à la fin les valeurs à copier dans GitHub, ainsi que les identifiants de l'administrateur et de l'accès HTTP.

Il reste ensuite à :

1. compléter les réglages SMTP (`MAIL_*`) dans `/var/www/netmobile/recette/shared/.env` ;
2. dans GitHub, ouvrir **Settings › Environments › recette** et ajouter les secrets `SSH_HOST`, `SSH_KNOWN_HOSTS`, `SSH_PRIVATE_KEY` et `HEALTH_BASIC_AUTH`. Supprimer ensuite la clé privée du serveur ;
3. dans **Settings › Secrets and variables › Actions › Variables**, créer `DEPLOY_RECETTE_ENABLED` = `true`.

### Publier

```bash
git checkout recette && git merge --ff-only dev && git push origin recette
```

Retour arrière : `sudo -u deploy sudo netmobile-deploy recette rollback`.
État : `sudo -u deploy sudo netmobile-deploy recette status`.

## Production — https://solution-netmobile.fr

Même serveur et même mécanisme que la recette, avec des ressources dédiées.

| Élément | Valeur |
|---|---|
| Branche | `prod` |
| Domaine | `solution-netmobile.fr` (`www.solution-netmobile.fr` redirigé vers le domaine nu) |
| Répertoire | `/var/www/netmobile/prod` |
| Utilisateur système / pool PHP-FPM | `netmobile-prod` (`/run/php/netmobile-prod.sock`) |
| Base MariaDB | `solution_netmobile_prod` (utilisateur `netmobile_prod`) |
| Accès | public, indexable, `APP_ENV=production` |
| Workflow | `.github/workflows/deploy-prod.yml`, environnement GitHub `prod`, variable `DEPLOY_PROD_ENABLED` |

### Installation sur le serveur (une seule fois)

Prérequis : les enregistrements DNS `solution-netmobile.fr` et `www.solution-netmobile.fr` pointent vers le serveur
(sinon le certificat Let's Encrypt ne peut pas être obtenu ; `www` est ignoré s'il ne résout pas).

```bash
git clone --branch prod https://github.com/SolutionNetmobile/solutionnetmobile.git /tmp/netmobile-prod
sudo bash /tmp/netmobile-prod/deploy/server/install.sh prod
```

Il reste ensuite à :

1. compléter les réglages SMTP (`MAIL_*`) dans `/var/www/netmobile/prod/shared/.env` ;
2. dans GitHub, créer l'environnement **prod** (branche autorisée : `prod`) et y ajouter les secrets `SSH_HOST`, `SSH_KNOWN_HOSTS` et `SSH_PRIVATE_KEY` affichés par le script. Supprimer ensuite la clé privée du serveur ;
3. créer la variable de dépôt `DEPLOY_PROD_ENABLED` = `true` ;
4. suivre la liste « Avant la mise en ligne » ci-dessous.

### Publier

```bash
git checkout prod && git merge --ff-only recette && git push origin prod
```

Retour arrière : `sudo -u deploy sudo netmobile-deploy prod rollback`.
État : `sudo -u deploy sudo netmobile-deploy prod status`.

---

# Déploiement manuel (autre serveur)

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
APP_URL=https://solution-netmobile.fr

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
    server_name solution-netmobile.fr;
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
6. Déclarer `https://solution-netmobile.fr/sitemap.xml` dans Google Search Console.
