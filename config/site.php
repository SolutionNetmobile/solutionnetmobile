<?php

/*
|--------------------------------------------------------------------------
| Solution Netmobile — site configuration
|--------------------------------------------------------------------------
|
| "defaults" are the fallback values of the editable settings stored in the
| `site_settings` table (see App\Services\SettingsService). The seeder copies
| them into the database; administrators then edit them from Filament.
|
| Brand colors are defined once in resources/css/app.css (@theme) for the
| public site, and below for the Filament panel.
|
*/

return [

    'brand' => [
        'primary' => '#0755C8',
        'secondary' => '#1674DB',
        'light' => '#EAF2FF',
        'lighter' => '#F3F7FF',
        'night' => '#07133D',
        'gray' => '#667085',
    ],

    'contact_retention_months' => (int) env('CONTACT_RETENTION_MONTHS', 36),

    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@solution-netmobile.fr'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    | Editable settings grouped by back-office section.
    */
    'defaults' => [

        'general' => [
            'site_name' => 'Solution Netmobile',
            'site_tagline' => 'Agence de conseil digital',
            'site_activities' => 'Conseil • Accompagnement • Production web & mobile',
            'logo' => null,
            'logo_footer' => null,
            'favicon' => null,
            'copyright' => 'Solution Netmobile. Tous droits réservés.',
            'company_legal_name' => 'Solution Netmobile',
            'company_legal_form' => 'Entreprise individuelle',
            'company_siret' => null,
            'company_address' => 'France',
            'company_publication_director' => 'Florent Andreu',
        ],

        'contact' => [
            'contact_email' => 'contact@solution-netmobile.fr',
            'contact_phone' => '+33 6 12 34 56 78',
            'contact_location' => 'France',
            'linkedin_url' => 'https://www.linkedin.com/',
            'notification_emails' => 'contact@solution-netmobile.fr',
            'contact_title' => 'Parlons de votre projet',
            'contact_subtitle' => 'Remplissez le formulaire ci-dessous : nous revenons vers vous sous 48 heures ouvrées.',
            'contact_success_message' => 'Merci, votre demande a bien été envoyée. Nous revenons vers vous très rapidement.',
            'contact_retention_months' => '36',
        ],

        'hero' => [
            'hero_kicker' => 'Agence de conseil digital',
            'hero_title' => 'Solution Netmobile,',
            'hero_title_highlight' => 'votre partenaire digital',
            'hero_description' => 'Conseil, accompagnement et production de solutions web et mobiles pour concrétiser vos projets digitaux.',
            'hero_primary_button' => 'Discuter de votre projet',
            'hero_secondary_button' => 'Nos prestations',
            'hero_image' => null,
        ],

        'sections' => [
            'services_kicker' => 'Nos expertises',
            'services_title' => 'Un accompagnement digital complet',
            'services_subtitle' => 'De la stratégie à la réalisation, nous vous aidons à tirer le meilleur du digital.',
            'methodology_kicker' => 'Notre méthodologie',
            'methodology_title' => 'Une démarche simple et efficace',
            'about_kicker' => 'À propos',
            'clients_title' => 'Ils nous font confiance',
        ],

        'cta' => [
            'cta_title' => 'Un projet digital ?',
            'cta_description' => 'Échangeons sur vos besoins et voyons ensemble comment Solution Netmobile peut vous accompagner.',
            'cta_button' => 'Nous contacter',
        ],

        'agency' => [
            'agency_name' => 'Solution Netmobile',
            'agency_description' => 'Une agence de conseil digital au service des entreprises et organisations qui souhaitent accélérer leurs projets digitaux, avec une approche pragmatique, accessible et orientée résultats.',
            'agency_presentation' => null,
            'leader_label' => 'Dirigée par',
            'leader_name' => 'Florent Andreu',
            'leader_role' => 'Dirigeant',
            'leader_description' => 'Un interlocuteur unique, engagé à vos côtés tout au long de votre projet.',
            'leader_photo' => null,
            'leader_email' => null,
            'leader_phone' => null,
            'leader_linkedin' => null,
            'agency_extra_info' => null,
        ],

        'seo' => [
            'seo_title' => 'Solution Netmobile — Agence de conseil digital, web & mobile',
            'seo_description' => 'Agence de conseil digital : stratégie, accompagnement et production de sites web et d’applications mobiles sur mesure pour vos projets.',
            'og_image' => null,
        ],
    ],

    /*
    | Lucide icons selectable in the back office (value => label).
    */
    'icons' => [
        'target' => 'Cible',
        'handshake' => 'Poignée de main',
        'monitor-smartphone' => 'Ordinateur et smartphone',
        'search' => 'Loupe',
        'lightbulb' => 'Ampoule',
        'code-xml' => 'Code',
        'rocket' => 'Fusée',
        'chart-line' => 'Graphique',
        'compass' => 'Boussole',
        'layers' => 'Calques',
        'palette' => 'Palette',
        'smartphone' => 'Smartphone',
        'globe' => 'Globe',
        'shield-check' => 'Sécurité',
        'users' => 'Équipe',
        'messages-square' => 'Échanges',
        'settings' => 'Réglages',
        'sparkles' => 'Étincelles',
        'calendar-check' => 'Calendrier',
        'clipboard-list' => 'Liste',
    ],
];
