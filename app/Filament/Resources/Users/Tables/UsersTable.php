<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->modifyQueryUsing(fn ($query) => $query->with('roles'))
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->sortable()->weight('bold'),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                TextColumn::make('roles.name')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RoleEnum::labelFor($state))
                    ->color(fn (string $state): string => RoleEnum::tryFrom($state)?->getColor() ?? 'gray'),
                IconColumn::make('is_active')->label('Actif')->boolean(),
                TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->since()
                    ->sortable()
                    ->placeholder('Jamais'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Statut du compte'),
                SelectFilter::make('roles')
                    ->label('Rôle')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => RoleEnum::labelFor($record->name)),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (User $record): string => $record->is_active ? 'Désactiver' : 'Réactiver')
                    ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn (User $record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->authorize('update')
                    ->hidden(fn (User $record): bool => $record->is(auth()->user()))
                    ->action(function (User $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()->title($record->is_active ? 'Compte réactivé' : 'Compte désactivé')->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
