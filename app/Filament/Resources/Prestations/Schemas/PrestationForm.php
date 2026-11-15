<?php

namespace App\Filament\Resources\Prestations\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrestationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Contenu')
                        ->columns(2)
                        ->schema([
                            Fields::titleWithSlug()->label('Nom'),
                            Fields::slug('/prestations/'),
                            Textarea::make('excerpt')
                                ->label('Description courte')
                                ->helperText('Affichée sur la carte de la page d’accueil.')
                                ->required()
                                ->maxLength(500)
                                ->rows(3)
                                ->columnSpanFull(),
                            Fields::richText('content', 'prestations/contenu')
                                ->label('Description détaillée')
                                ->helperText('Affichée sur la page de détail de la prestation.')
                                ->columnSpanFull(),
                        ]),
                    Section::make('Référencement (SEO)')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            TextInput::make('meta_title')
                                ->label('Balise title')
                                ->maxLength(70)
                                ->helperText('Laisser vide pour utiliser le nom de la prestation.'),
                            Textarea::make('meta_description')
                                ->label('Meta description')
                                ->maxLength(160)
                                ->rows(2)
                                ->helperText('160 caractères maximum. Laisser vide pour utiliser la description courte.'),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Publication')
                        ->schema([
                            Toggle::make('is_published')
                                ->label('Publiée sur le site')
                                ->default(true),
                            TextInput::make('sort_order')
                                ->label('Ordre d’affichage')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ]),
                    Section::make('Visuel')
                        ->schema([
                            Fields::icon(),
                            Fields::image('image', 'prestations')->label('Image (facultative)'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
