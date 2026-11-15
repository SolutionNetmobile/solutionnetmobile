<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Enums\PageStatus;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Page $record): string => '/pages/'.$record->slug),
                TextColumn::make('status')->label('Statut')->badge()->sortable(),
                TextColumn::make('published_at')
                    ->label('Publication')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Immédiate'),
                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Statut')->options(PageStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Voir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Page $record): string => route('pages.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Page $record): bool => $record->status === PageStatus::Published && ! $record->trashed()),
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
            ]);
    }
}
