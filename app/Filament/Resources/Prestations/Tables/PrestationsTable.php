<?php

namespace App\Filament\Resources\Prestations\Tables;

use App\Models\Prestation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PrestationsTable
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
                TextColumn::make('title')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Prestation $record): string => '/prestations/'.$record->slug),
                TextColumn::make('excerpt')
                    ->label('Description courte')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(),
                ToggleColumn::make('is_published')->label('Publiée'),
                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Publication'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Voir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Prestation $record): string => route('prestations.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Prestation $record): bool => $record->is_published && ! $record->trashed()),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucune prestation')
            ->emptyStateDescription('Créez votre première prestation pour l’afficher sur le site.');
    }
}
