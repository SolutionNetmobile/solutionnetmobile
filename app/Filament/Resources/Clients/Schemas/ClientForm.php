<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Client')
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du client')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('website_url')
                            ->label('Site web du client')
                            ->url()
                            ->rule('starts_with:http://,https://')
                            ->maxLength(255)
                            ->placeholder('https://')
                            ->helperText('Facultatif : le logo devient alors cliquable.'),
                        Textarea::make('description')
                            ->label('Description (facultative)')
                            ->rows(3)
                            ->maxLength(1000),
                    ]),
                Section::make('Affichage')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Fields::image('logo', 'clients', allowSvg: true, maxKb: 2048)
                            ->label('Logo')
                            ->helperText('Format horizontal recommandé. Sans logo, le nom du client est affiché.'),
                        Toggle::make('is_active')->label('Affiché sur le site')->default(true),
                        TextInput::make('sort_order')
                            ->label('Ordre d’affichage')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }
}
