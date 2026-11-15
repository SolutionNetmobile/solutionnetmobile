<?php

namespace App\Filament\Pages;

use App\Filament\Support\Fields;
use App\Filament\Support\SettingsPage;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSiteSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Paramètres du site';

    protected static ?string $title = 'Paramètres du site';

    protected static ?string $slug = 'parametres-du-site';

    protected static function permission(): string
    {
        return 'settings.manage';
    }

    protected function groups(): array
    {
        return ['general', 'contact', 'hero', 'sections', 'cta', 'seo'];
    }

    protected function formComponents(): array
    {
        return [
            Tabs::make('settings')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Identité')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('Logos & favicon')
                                ->columns(3)
                                ->schema([
                                    Fields::image('logo', 'settings', allowSvg: true, maxKb: 1024)->label('Logo principal')->helperText('Laisser vide pour utiliser le logo vectoriel par défaut.'),
                                    Fields::image('logo_footer', 'settings', allowSvg: true, maxKb: 1024)->label('Logo du pied de page')->helperText('Laisser vide pour réutiliser le logo principal.'),
                                    Fields::image('favicon', 'settings', allowSvg: true, maxKb: 512)->label('Favicon')->helperText('Carré, 512×512 px recommandé (PNG ou SVG).'),
                                ]),
                            Section::make('Entreprise')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('site_name')->label('Nom commercial')->required()->maxLength(100),
                                    TextInput::make('site_tagline')->label('Description courte')->maxLength(150),
                                    TextInput::make('site_activities')->label('Activités (pied de page)')->maxLength(200)->columnSpanFull(),
                                    TextInput::make('company_legal_name')->label('Raison sociale')->maxLength(150),
                                    TextInput::make('company_legal_form')->label('Forme juridique')->maxLength(100),
                                    TextInput::make('company_siret')->label('SIRET')->maxLength(20),
                                    TextInput::make('company_publication_director')->label('Directeur de la publication')->maxLength(100),
                                    Textarea::make('company_address')->label('Adresse du siège')->rows(2)->maxLength(300)->columnSpanFull(),
                                    TextInput::make('copyright')->label('Mention de copyright')->helperText('L’année courante est ajoutée automatiquement devant ce texte.')->maxLength(150)->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Coordonnées & formulaire')
                        ->icon('heroicon-o-envelope')
                        ->schema([
                            Section::make('Coordonnées publiques')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('contact_email')->label('Adresse e-mail')->email()->required()->maxLength(255),
                                    TextInput::make('contact_phone')->label('Téléphone')->tel()->maxLength(30),
                                    TextInput::make('contact_location')->label('Adresse ou localisation')->maxLength(150),
                                    TextInput::make('linkedin_url')->label('URL LinkedIn')->url()->rule('starts_with:https://')->maxLength(255),
                                ]),
                            Section::make('Formulaire de contact')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('notification_emails')
                                        ->label('Destinataires des notifications')
                                        ->helperText('Une ou plusieurs adresses séparées par des virgules.')
                                        ->required()
                                        ->maxLength(500)
                                        ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) as $email) {
                                                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                                                    $fail("« {$email} » n’est pas une adresse e-mail valide.");
                                                }
                                            }
                                        })
                                        ->columnSpanFull(),
                                    TextInput::make('contact_title')->label('Titre de la section contact')->maxLength(150),
                                    TextInput::make('contact_retention_months')
                                        ->label('Durée de conservation des demandes (mois)')
                                        ->helperText('RGPD : les demandes plus anciennes sont supprimées automatiquement chaque nuit.')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(120)
                                        ->required(),
                                    Textarea::make('contact_subtitle')->label('Texte d’introduction')->rows(2)->maxLength(300)->columnSpanFull(),
                                    Textarea::make('contact_success_message')->label('Message de confirmation après envoi')->rows(2)->maxLength(300)->required()->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Accueil')
                        ->icon('heroicon-o-home')
                        ->schema([
                            Section::make('Section principale (hero)')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('hero_kicker')->label('Sur-titre')->maxLength(80),
                                    TextInput::make('hero_title')->label('Titre')->required()->maxLength(120),
                                    TextInput::make('hero_title_highlight')->label('Titre — partie mise en évidence (bleu)')->maxLength(120),
                                    Textarea::make('hero_description')->label('Description')->rows(2)->maxLength(300),
                                    TextInput::make('hero_primary_button')->label('Bouton principal')->maxLength(50),
                                    TextInput::make('hero_secondary_button')->label('Bouton secondaire')->maxLength(50),
                                    Fields::image('hero_image', 'settings')->label('Illustration')->helperText('Laisser vide pour utiliser l’illustration par défaut (ordinateur et smartphone).')->columnSpanFull(),
                                ]),
                            Section::make('Titres des sections')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('services_kicker')->label('Prestations — sur-titre')->maxLength(80),
                                    TextInput::make('services_title')->label('Prestations — titre')->maxLength(120),
                                    Textarea::make('services_subtitle')->label('Prestations — sous-titre')->rows(2)->maxLength(250)->columnSpanFull(),
                                    TextInput::make('methodology_kicker')->label('Méthodologie — sur-titre')->maxLength(80),
                                    TextInput::make('methodology_title')->label('Méthodologie — titre')->maxLength(120),
                                    TextInput::make('about_kicker')->label('À propos — sur-titre')->maxLength(80),
                                    TextInput::make('clients_title')->label('Références — titre')->maxLength(120),
                                ]),
                            Section::make('Appel à l’action (CTA)')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('cta_title')->label('Titre')->maxLength(120),
                                    TextInput::make('cta_button')->label('Texte du bouton')->maxLength(50),
                                    Textarea::make('cta_description')->label('Description')->rows(2)->maxLength(300)->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Référencement')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('seo_title')->label('Balise title de la page d’accueil')->maxLength(70)->columnSpanFull(),
                                Textarea::make('seo_description')->label('Meta description par défaut')->rows(3)->maxLength(160)->columnSpanFull(),
                                Fields::image('og_image', 'settings')->label('Image de partage (Open Graph)')->helperText('1200×630 px recommandé.'),
                            ]),
                        ]),
                ]),
        ];
    }
}
