# Solution Netmobile — site vitrine administrable

Site vitrine de l'agence de conseil digital **Solution Netmobile**, avec un back-office
complet (CMS, gestion des prospects, utilisateurs et permissions).

| Couche | Technologie |
| --- | --- |
| Backend | Laravel 13 (PHP ≥ 8.4) |
| Back-office | Filament 5 (`/admin`, en français) |
| Front public | Blade + Tailwind CSS 4 + Alpine.js (rendu serveur, aucune SPA) |
| Base de données | MariaDB ≥ 10.6 (moteur InnoDB, utf8mb4) |
| Rôles & permissions | spatie/laravel-permission |
| Icônes | Lucide (`mallardduck/blade-lucide-icons`) et Heroicons (back-office) |
| Police | Outfit, auto-hébergée (`@fontsource-variable/outfit`, aucun appel à Google Fonts) |

Documentation complémentaire :

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) — architecture, modèle de données, rôles.
- [docs/DEPLOIEMENT.md](docs/DEPLOIEMENT.md) — mise en production.

---

## Installation locale

### Prérequis

- PHP 8.4 ou plus avec les extensions `intl`, `pdo_mysql`, `gd` (avec WebP), `mbstring`, `fileinfo`, `zip`.
- Composer 2, Node.js 20 ou plus et npm.
- Un serveur MariaDB (ou MySQL 8).

### Étapes

```bash
# 1. Dépendances
composer install
npm install

# 2. Configuration
cp .env.example .env
php artisan key:generate
```

Renseignez ensuite dans `.env` :

```dotenv
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306            # 3307 avec MariaDB sous WAMP
DB_DATABASE=solution_netmobile
DB_USERNAME=root
DB_PASSWORD=

ADMIN_EMAIL=admin@solution-netmobile.fr
ADMIN_PASSWORD=UnMotDePasseSolide2026!   # vide = généré et affiché par le seeder
```

```bash
# 3. Base de données (créez-la d'abord en utf8mb4)
mysql -u root -e "CREATE DATABASE solution_netmobile CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate --seed

# 4. Lien public pour les médias téléversés
php artisan storage:link

# 5. Assets et serveur
npm run build          # ou « npm run dev » pendant le développement
php artisan serve
```

- Site public : <http://127.0.0.1:8000>
- Back-office : <http://127.0.0.1:8000/admin> (identifiants `ADMIN_EMAIL` / `ADMIN_PASSWORD`)

Le seeder crée : les rôles et permissions, le super administrateur, les paramètres du site,
les 3 prestations, les 4 étapes de méthodologie, 4 logos clients de démonstration, les pages
Mentions légales et Politique de confidentialité, ainsi que (hors production) 28 prospects de démonstration.

### Tests

Les tests utilisent une base MariaDB dédiée, `solution_netmobile_test` (voir `phpunit.xml`) :

```bash
mysql -u root -e "CREATE DATABASE solution_netmobile_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan test
```

### Qualité du code

```bash
vendor/bin/pint          # formatage PSR-12 / Laravel
```

---

## Fonctionnalités

**Site public** : header avec menu mobile et état actif sur la section visible, hero, expertises,
méthodologie, à propos, références clients, CTA, formulaire de contact et footer. On y trouve aussi
les pages de détail des prestations, les pages légales, le sitemap XML, le robots.txt, les balises Open Graph et Schema.org.

**Formulaire de contact** :
- validation côté navigateur et côté serveur (Form Request) ;
- protection CSRF ;
- champ piège anti-robot et délai minimal de saisie signé ;
- limitation à 3 envois par minute et 20 par jour par adresse IP ;
- enregistrement en base et notification e-mail aux destinataires configurés.

**Back-office** :
- tableau de bord : statistiques et 3 graphiques alimentés par MariaDB ;
- CRUD des prestations, de la méthodologie, des clients et des pages, avec réorganisation par glisser-déposer ;
- présentation de l'agence et paramètres du site ;
- gestion des prospects : statuts, notes internes, historique des échanges, onglets, filtres, export CSV, archivage et effacement RGPD ;
- utilisateurs et rôles (Super administrateur, Administrateur, Éditeur).

**RGPD** : consentement explicite, collecte minimale (pas d'adresse IP stockée), droit à l'effacement,
purge automatique des demandes au-delà de la durée de conservation configurable (commande `contacts:purge`, planifiée chaque nuit).
