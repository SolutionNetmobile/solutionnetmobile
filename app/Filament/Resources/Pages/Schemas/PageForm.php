<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PageStatus;
use App\Filament\Support\Fields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
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
                            Fields::titleWithSlug(),
                            Fields::slug('/pages/'),
                            Fields::richText('content', 'pages/contenu')
                                ->label('Contenu')
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    Section::make('Référencement (SEO)')
                        ->collapsible()
                        ->schema([
                            TextInput::make('meta_title')
                                ->label('Balise title')
                                ->maxLength(70)
                                ->helperText('Laisser vide pour utiliser le titre de la page.'),
                            Textarea::make('meta_description')
                                ->label('Meta description')
                                ->maxLength(160)
                                ->rows(2),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Publication')
                        ->schema([
                            Select::make('status')
                                ->label('Statut')
                                ->options(PageStatus::class)
                                ->default(PageStatus::Draft)
                                ->required()
                                ->native(false),
                            DateTimePicker::make('published_at')
                                ->label('Date de publication')
                                ->seconds(false)
                                ->native(false)
                                ->displayFormat('d/m/Y H:i')
                                ->helperText('Laisser vide pour publier immédiatement. Une date future programme la publication.'),
                        ]),
                    Section::make('Image principale')
                        ->schema([
                            Fields::image('image', 'pages')->hiddenLabel(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
