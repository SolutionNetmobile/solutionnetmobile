# Architecture — Solution Netmobile

## Vue d'ensemble

```
Navigateur ──► Laravel (routes/web.php)
                 ├─ Site public : contrôleurs fins → services → vues Blade (composants)
                 └─ /admin : panel Filament 5 (ressources, pages, widgets)
                        │
                 Services métier (app/Services)
                        │
                 Eloquent ──► MariaDB (InnoDB, utf8mb4)
                 Cache (database / redis) ← paramètres et contenus publics
```

## Organisation du code

| Dossier | Rôle |
| --- | --- |
| `app/Enums` | Statuts des prospects, types de projet, budgets, types de notes, statuts de page, rôles. |
| `app/Models` | Modèles Eloquent ; `Concerns/` : vidage du cache public et URLs des médias. |
| `app/Http/Controllers` | `HomeController`, `PrestationController`, `PageController`, `ContactController`, `SeoController`. |
| `app/Http/Requests` | `StoreContactRequest` : validation, nettoyage et anti-spam du formulaire. |
| `app/Services` | `SettingsService` (paramètres en cache), `PublicContentService` (contenus publics en cache), `ContactService` (enregistrement, notification, purge RGPD), `ContactExporter` (CSV), `DashboardStatistics`, `ImageOptimizer` (WebP, contrôle des SVG). |
| `app/Policies` | `ModulePolicy` traduit les droits Laravel (viewAny, create…) en permissions `module.action`. |
| `app/Support` | `Permissions` (source unique des permissions par rôle), `Html` (nettoyage du texte riche). |
| `app/Filament` | Ressources, pages de paramètres, widgets, champs réutilisables (`Support/Fields`). |
| `resources/views/components` | Composants Blade : `layouts/app`, `site/header`, `site/footer`, `site/logo`, `site/icon`, `button`, `section-heading`, `prestation-card`, `client-logo`, `cta`, `contact-form`, `form/field`, `hero-illustration`. |
| `resources/views/pages` | Accueil, détail d'une prestation, page de contenu. |
| `config/site.php` | Couleurs de marque, valeurs par défaut des paramètres, icônes disponibles. |

Les couleurs du site public sont définies une seule fois dans `resources/css/app.css` (`@theme`).
Le panel Filament lit `config/site.php`.

## Modèle de données

| Table | Contenu | Points clés |
| --- | --- | --- |
| `users` | Comptes du back-office | `is_active`, `last_login_at` |
| `roles`, `permissions`, `model_has_roles`, `role_has_permissions`… | Spatie Permission | 3 rôles, 28 permissions |
| `prestations` | Prestations | `slug` unique, `sort_order`, `is_published`, SEO, soft deletes |
| `methodologies` | Étapes de la démarche | `sort_order`, `is_active` |
| `clients` | Références clients | logo, URL, `sort_order`, `is_active`, soft deletes |
| `pages` | Pages de contenu (légales…) | `slug` unique, `status` (brouillon/publié), `published_at`, SEO, soft deletes |
| `contacts` | Demandes / prospects | statut, `archived_at`, `privacy_accepted_at`, soft deletes, index de recherche |
| `contact_notes` | Notes internes et historique | FK `contact_id` (cascade), FK `user_id` (null on delete), `type` |
| `site_settings` | Paramètres clé/valeur | `key` unique, `group` |

Relations : `Contact hasMany ContactNote`, `ContactNote belongsTo Contact` et `belongsTo User (author)`,
`User` ↔ `Role` via Spatie.

Chaque changement de statut d'un prospect crée automatiquement une entrée `status_change` dans son historique.

## Rôles et permissions

| Permission | Super admin | Admin | Éditeur |
| --- | :---: | :---: | :---: |
| Prestations, méthodologie, clients, pages (consulter/créer/modifier/supprimer) | ✓ | ✓ | ✓ |
| Présentation de l'agence | ✓ | ✓ | ✓ |
| Prospects (consulter/modifier/supprimer/exporter) | ✓ | ✓ | — |
| Paramètres du site | ✓ | ✓ | — |
| Utilisateurs | ✓ | ✓ (sauf super admins) | — |
| Rôles & permissions | ✓ | — | — |

Les permissions des rôles Administrateur et Éditeur se modifient dans *Administration → Rôles & permissions*.
Le super administrateur a tous les droits (`Gate::before`). Un compte désactivé perd immédiatement l'accès.

## Cache

- `site_settings.all` : paramètres, vidé à chaque enregistrement depuis l'administration.
- `public.prestations`, `public.methodologies`, `public.clients` : vidés automatiquement à chaque création,
  modification, suppression ou restauration (trait `FlushesPublicContentCache`).
- Le cache ne stocke que des tableaux, jamais d'objets PHP sérialisés (`cache.serializable_classes = false`).

## Sécurité

- CSRF sur tous les formulaires ; validation serveur systématique ; requêtes Eloquent paramétrées.
- Texte riche nettoyé par `symfony/html-sanitizer` avant affichage ; tout le reste est échappé par Blade.
- Téléversements : types MIME autorisés, taille maximale, renommage aléatoire, conversion des JPG/PNG en WebP,
  refus des SVG contenant scripts, gestionnaires d'événements ou liens externes.
- Connexion à l'administration limitée en nombre de tentatives (Filament). Mots de passe d'au moins
  12 caractères avec majuscules, minuscules et chiffres.
- En-têtes `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, et HSTS en HTTPS.
- Export CSV protégé contre l'injection de formules.
