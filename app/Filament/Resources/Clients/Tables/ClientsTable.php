<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Models\Client;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable()->width('3rem'),
                ImageColumn::make('logo')->label('Logo')->disk('public')->imageHeight(36),
                TextColumn::make('name')->label('Client')->searchable()->sortable()->weight('bold'),
                TextColumn::make('website_url')
                    ->label('Site web')
                    ->url(fn (Client $record): ?string => $record->website_url)
                    ->openUrlInNewTab()
                    ->limit(40)
                    ->placeholder('—'),
                ToggleColumn::make('is_active')->label('Affiché'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Affichage'),
                TrashedFilter::make(),
            ])
            ->recordActions([
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
            ->emptyStateHeading('Aucun client')
            ->emptyStateDescription('Ajoutez les logos de vos clients pour la bande « Ils nous font confiance ».');
    }
}
