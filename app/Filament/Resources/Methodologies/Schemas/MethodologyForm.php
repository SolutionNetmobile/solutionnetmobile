<?php

namespace App\Filament\Resources\Methodologies\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MethodologyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Étape')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')
                        ->label('Titre de l’étape')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('description')
                        ->label('Description')
                        ->required()
                        ->maxLength(500),
                    Fields::icon(),
                    TextInput::make('sort_order')
                        ->label('Numéro / ordre')
                        ->helperText('Le numéro affiché sur le site suit l’ordre des étapes actives.')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Étape active')
                        ->default(true),
                ]),
        ]);
    }
}
