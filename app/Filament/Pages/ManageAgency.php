<?php

namespace App\Filament\Pages;

use App\Filament\Support\Fields;
use App\Filament\Support\SettingsPage;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageAgency extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Présentation de l’agence';

    protected static ?string $title = 'Présentation de l’agence';

    protected static ?string $slug = 'presentation-agence';

    protected static function permission(): string
    {
        return 'agency.manage';
    }

    protected function groups(): array
    {
        return ['agency'];
    }

    protected function formComponents(): array
    {
        return [
            Section::make('L’agence')
                ->description('Section « À propos » de la page d’accueil.')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('agency_name')->label('Nom de l’agence')->required()->maxLength(100),
                    Textarea::make('agency_description')->label('Description')->required()->rows(3)->maxLength(600),
                    Fields::richText('agency_presentation', 'settings/contenu')
                        ->label('Présentation générale (facultative)')
                        ->helperText('Texte complémentaire affiché sous la description.'),
                ]),
            Section::make('Le dirigeant')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('leader_name')->label('Nom du dirigeant')->required()->maxLength(100),
                    TextInput::make('leader_role')->label('Fonction')->maxLength(100),
                    TextInput::make('leader_label')->label('Libellé d’introduction')->placeholder('Dirigée par')->maxLength(50),
                    Fields::image('leader_photo', 'settings', maxKb: 2048)->label('Photo')->avatar()->helperText('Format carré recommandé.'),
                    Textarea::make('leader_description')->label('Description')->rows(2)->maxLength(300)->columnSpanFull(),
                    TextInput::make('leader_email')->label('E-mail (facultatif)')->email()->maxLength(255),
                    TextInput::make('leader_phone')->label('Téléphone (facultatif)')->tel()->maxLength(30),
                    TextInput::make('leader_linkedin')->label('Profil LinkedIn (facultatif)')->url()->rule('starts_with:https://')->maxLength(255),
                    Textarea::make('agency_extra_info')->label('Informations complémentaires')->rows(2)->maxLength(500)->columnSpanFull(),
                ]),
        ];
    }
}
