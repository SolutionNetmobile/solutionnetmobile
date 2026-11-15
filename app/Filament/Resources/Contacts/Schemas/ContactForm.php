<?php

namespace App\Filament\Resources\Contacts\Schemas;

use App\Enums\Budget;
use App\Enums\ContactStatus;
use App\Enums\ProjectType;
use App\Http\Requests\StoreContactRequest;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Coordonnées')
                    ->columns(2)
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextInput::make('last_name')->label('Nom')->required()->maxLength(100),
                        TextInput::make('first_name')->label('Prénom')->required()->maxLength(100),
                        TextInput::make('company')->label('Entreprise')->maxLength(150),
                        TextInput::make('email')->label('E-mail')->email()->required()->maxLength(255),
                        TextInput::make('phone')->label('Téléphone')->tel()->regex(StoreContactRequest::PHONE_PATTERN),
                        Select::make('project_type')->label('Type de projet')->options(ProjectType::class)->required()->native(false),
                        Select::make('budget')->label('Budget estimatif')->options(Budget::class)->native(false),
                        Textarea::make('message')->label('Message')->required()->rows(6)->maxLength(5000)->columnSpanFull(),
                    ]),
                Section::make('Suivi')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options(ContactStatus::class)
                            ->required()
                            ->native(false)
                            ->helperText('Chaque changement est tracé dans l’historique.'),
                    ]),
            ]);
    }
}
