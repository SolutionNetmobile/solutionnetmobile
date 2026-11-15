<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Contacts\ContactResource;
use App\Models\Contact;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestContacts extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Dernières demandes reçues';

    public static function canView(): bool
    {
        return auth()->user()?->can('contacts.view') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Contact::query()->latest()->limit(8))
            ->paginated(false)
            ->recordUrl(fn (Contact $record): string => ContactResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('created_at')->label('Reçue le')->dateTime('d/m/Y H:i'),
                TextColumn::make('last_name')
                    ->label('Prospect')
                    ->formatStateUsing(fn (Contact $record): string => $record->full_name)
                    ->description(fn (Contact $record): ?string => $record->company)
                    ->weight('bold'),
                TextColumn::make('project_type')->label('Projet')->badge()->color('gray'),
                TextColumn::make('status')->label('Statut')->badge(),
            ])
            ->recordActions([
                ViewAction::make()->url(fn (Contact $record): string => ContactResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('Aucune demande pour le moment');
    }
}
