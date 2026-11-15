<?php

namespace App\Filament\Resources\Contacts\Schemas;

use App\Models\Contact;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Demande')
                    ->columns(2)
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextEntry::make('full_name')->label('Nom'),
                        TextEntry::make('company')->label('Entreprise')->placeholder('—'),
                        TextEntry::make('email')
                            ->label('E-mail')
                            ->copyable()
                            ->url(fn (Contact $record): string => 'mailto:'.$record->email),
                        TextEntry::make('phone')
                            ->label('Téléphone')
                            ->placeholder('—')
                            ->url(fn (Contact $record): ?string => $record->phone ? 'tel:'.preg_replace('/[^0-9+]/', '', $record->phone) : null),
                        TextEntry::make('project_type')->label('Type de projet')->badge()->color('primary'),
                        TextEntry::make('budget')->label('Budget estimatif')->placeholder('Non précisé'),
                        TextEntry::make('message')->label('Message')->columnSpanFull()->prose(),
                    ]),
                Section::make('Suivi')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextEntry::make('status')->label('Statut')->badge(),
                        TextEntry::make('created_at')->label('Reçue le')->dateTime('d/m/Y à H:i'),
                        TextEntry::make('privacy_accepted_at')->label('Consentement RGPD')->dateTime('d/m/Y à H:i'),
                        TextEntry::make('archived_at')->label('Archivée le')->dateTime('d/m/Y à H:i')->visible(fn (Contact $record): bool => $record->isArchived()),
                        TextEntry::make('updated_at')->label('Dernière mise à jour')->since(),
                    ]),
            ]);
    }
}
