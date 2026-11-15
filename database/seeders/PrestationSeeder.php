<?php

namespace Database\Seeders;

use App\Models\Prestation;
use Illuminate\Database\Seeder;

class PrestationSeeder extends Seeder
{
    public function run(): void
    {
        $prestations = [
            [
                'title' => 'Conseil digital',
                'slug' => 'conseil-digital',
                'icon' => 'target',
                'excerpt' => 'Définir la bonne stratégie, identifier les leviers pertinents et structurer votre projet.',
                'content' => <<<'HTML'
                    <p>Avant d’investir dans un outil, il faut savoir précisément ce que l’on cherche à accomplir. Nous vous aidons à poser un diagnostic clair de votre situation digitale et à construire une feuille de route réaliste.</p>
                    <h2>Ce que nous faisons</h2>
                    <ul>
                        <li>Audit de votre présence digitale et de vos outils existants</li>
                        <li>Définition des objectifs, des cibles et des indicateurs de réussite</li>
                        <li>Identification des leviers prioritaires (site, application, automatisation, acquisition)</li>
                        <li>Cadrage fonctionnel et estimation budgétaire</li>
                    </ul>
                    <h2>Ce que vous obtenez</h2>
                    <p>Une feuille de route priorisée, un cahier des charges exploitable et une vision claire des investissements à engager.</p>
                    HTML,
                'meta_description' => 'Conseil digital : audit, stratégie, cadrage et feuille de route pour structurer votre projet web ou mobile.',
            ],
            [
                'title' => 'Accompagnement digital',
                'slug' => 'accompagnement-digital',
                'icon' => 'handshake',
                'excerpt' => 'Un suivi de proximité pour piloter, prioriser et réussir votre transformation digitale.',
                'content' => <<<'HTML'
                    <p>Un projet digital réussi se pilote dans la durée. Nous restons à vos côtés pour arbitrer, prioriser et faire avancer votre projet avec vos équipes et vos prestataires.</p>
                    <h2>Ce que nous faisons</h2>
                    <ul>
                        <li>Pilotage de projet et coordination des intervenants</li>
                        <li>Assistance à maîtrise d’ouvrage (AMOA)</li>
                        <li>Recette, suivi qualité et accompagnement au changement</li>
                        <li>Formation et montée en compétences de vos équipes</li>
                    </ul>
                    <h2>Ce que vous obtenez</h2>
                    <p>Un interlocuteur unique, des décisions éclairées et un projet qui tient ses délais et son budget.</p>
                    HTML,
                'meta_description' => 'Accompagnement digital : pilotage de projet, AMOA, recette et conduite du changement au plus près de vos équipes.',
            ],
            [
                'title' => 'Production web & mobile',
                'slug' => 'production-web-mobile',
                'icon' => 'monitor-smartphone',
                'excerpt' => 'Conception, design et développement de sites web et d’applications mobiles sur mesure.',
                'content' => <<<'HTML'
                    <p>De la maquette à la mise en ligne, nous concevons et développons des solutions web et mobiles performantes, sécurisées et faciles à faire évoluer.</p>
                    <h2>Ce que nous faisons</h2>
                    <ul>
                        <li>Sites vitrines et sites administrables</li>
                        <li>Applications web métier et extranets</li>
                        <li>Applications mobiles iOS et Android</li>
                        <li>Maintenance, hébergement et évolutions</li>
                    </ul>
                    <h2>Ce que vous obtenez</h2>
                    <p>Une solution sur mesure, responsive, optimisée pour le référencement et pensée pour vos utilisateurs.</p>
                    HTML,
                'meta_description' => 'Production web et mobile : conception, design et développement de sites web et d’applications mobiles sur mesure.',
            ],
        ];

        foreach ($prestations as $index => $prestation) {
            Prestation::query()->updateOrCreate(
                ['slug' => $prestation['slug']],
                $prestation + ['sort_order' => $index + 1, 'is_published' => true],
            );
        }
    }
}
