<?php

namespace App\Filament\Resources\Methodologies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MethodologiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable()->width('3rem'),
                IconColumn::make('icon')
                    ->label('Icône')
                    ->icon(fn (?string $state): string => 'lucide-'.($state ?: 'sparkles'))
                    ->color('primary'),
                TextColumn::make('title')->label('Titre')->searchable()->weight('bold'),
                TextColumn::make('description')->label('Description')->searchable()->wrap(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Statut'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucune étape')
            ->emptyStateDescription('Ajoutez les étapes de votre démarche.');
    }
}
