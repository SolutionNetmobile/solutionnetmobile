<?php

namespace App\Filament\Resources\Contacts\Tables;

use App\Enums\ContactStatus;
use App\Enums\ProjectType;
use App\Filament\Resources\Contacts\ContactActions;
use App\Models\Contact;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Contact $record): string => route('filament.admin.resources.contacts.view', $record))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Reçue le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Prospect')
                    ->formatStateUsing(fn (Contact $record): string => $record->full_name)
                    ->description(fn (Contact $record): ?string => $record->company)
                    ->searchable(['last_name', 'first_name', 'company'])
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('project_type')
                    ->label('Projet')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('budget')
                    ->label('Budget')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('notes_count')
                    ->label('Échanges')
                    ->counts('notes')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ContactStatus::class)
                    ->multiple(),
                SelectFilter::make('project_type')
                    ->label('Type de projet')
                    ->options(ProjectType::class),
                Filter::make('created_at')
                    ->label('Période de réception')
                    ->schema([
                        DatePicker::make('from')->label('Du'),
                        DatePicker::make('until')->label('Au'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))),
                TrashedFilter::make()->label('Corbeille'),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    ContactActions::changeStatus(),
                    EditAction::make(),
                    ContactActions::archive(),
                    ContactActions::unarchive(),
                    DeleteAction::make()->label('Mettre à la corbeille'),
                    RestoreAction::make(),
                    ContactActions::erase(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ContactActions::exportSelected(),
                    ContactActions::archiveSelected(),
                    DeleteBulkAction::make()->label('Mettre à la corbeille'),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-inbox')
            ->emptyStateHeading('Aucune demande')
            ->emptyStateDescription('Les demandes envoyées depuis le formulaire de contact apparaîtront ici.');
    }
}
