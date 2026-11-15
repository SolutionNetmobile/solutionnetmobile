<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Legal pages. The content is a starting point that must be reviewed and
     * completed (SIRET, host…) from the back office before going live.
     */
    public function run(): void
    {
        Page::query()->firstOrCreate(['slug' => Page::LEGAL_NOTICE], [
            'title' => 'Mentions légales',
            'status' => PageStatus::Published,
            'published_at' => now(),
            'meta_description' => 'Mentions légales du site Solution Netmobile : éditeur, hébergeur, propriété intellectuelle.',
            'content' => <<<'HTML'
                <h2>Éditeur du site</h2>
                <p>Le présent site est édité par <strong>Solution Netmobile</strong>, agence de conseil digital. Les informations légales de l’éditeur (raison sociale, SIRET, siège, directeur de la publication) figurent dans l’encadré ci-dessus.</p>
                <h2>Hébergement</h2>
                <p><em>Nom, adresse et téléphone de l’hébergeur : à compléter depuis l’administration.</em></p>
                <h2>Propriété intellectuelle</h2>
                <p>L’ensemble des contenus de ce site (textes, illustrations, logos, éléments graphiques) est protégé par le droit de la propriété intellectuelle. Toute reproduction, totale ou partielle, sans autorisation écrite préalable est interdite.</p>
                <h2>Responsabilité</h2>
                <p>Solution Netmobile s’efforce de fournir des informations exactes et à jour, sans pouvoir garantir l’absence d’erreurs ou d’omissions. Les liens vers des sites tiers n’engagent pas la responsabilité de l’éditeur.</p>
                <h2>Données personnelles</h2>
                <p>Le traitement des données personnelles collectées via ce site est décrit dans notre <a href="/pages/politique-de-confidentialite">politique de confidentialité</a>.</p>
                HTML,
        ]);

        Page::query()->firstOrCreate(['slug' => Page::PRIVACY_POLICY], [
            'title' => 'Politique de confidentialité',
            'status' => PageStatus::Published,
            'published_at' => now(),
            'meta_description' => 'Politique de confidentialité de Solution Netmobile : données collectées, finalités, durée de conservation et droits RGPD.',
            'content' => <<<'HTML'
                <p>Solution Netmobile attache une grande importance à la protection de vos données personnelles. Cette politique explique quelles données nous collectons, pourquoi, et comment exercer vos droits conformément au Règlement général sur la protection des données (RGPD).</p>
                <h2>Responsable du traitement</h2>
                <p>Solution Netmobile, représentée par Florent Andreu — contact@solution-netmobile.fr.</p>
                <h2>Données collectées</h2>
                <p>Via le formulaire de contact, nous collectons uniquement : nom, prénom, entreprise (facultatif), adresse e-mail, téléphone (facultatif), type de projet, budget estimatif (facultatif) et votre message.</p>
                <h2>Finalités et base légale</h2>
                <p>Ces données sont utilisées exclusivement pour répondre à votre demande et assurer le suivi de la relation commerciale. Le traitement repose sur votre consentement, recueilli lors de l’envoi du formulaire, et sur l’intérêt légitime de Solution Netmobile à répondre aux demandes qui lui sont adressées.</p>
                <h2>Destinataires</h2>
                <p>Vos données sont destinées aux seules personnes habilitées de Solution Netmobile. Elles ne sont ni vendues, ni cédées à des tiers.</p>
                <h2>Durée de conservation</h2>
                <p>Les demandes de contact sont conservées pendant une durée maximale de 36 mois à compter du dernier échange, puis supprimées automatiquement.</p>
                <h2>Vos droits</h2>
                <p>Vous disposez d’un droit d’accès, de rectification, d’effacement, de limitation, d’opposition et de portabilité de vos données. Pour exercer ces droits, écrivez à contact@solution-netmobile.fr. Vous pouvez également introduire une réclamation auprès de la CNIL (www.cnil.fr).</p>
                <h2>Cookies</h2>
                <p>Ce site n’utilise que des cookies strictement nécessaires à son fonctionnement (session et sécurité des formulaires). Aucun cookie publicitaire ou de mesure d’audience tiers n’est déposé.</p>
                HTML,
        ]);
    }
}
