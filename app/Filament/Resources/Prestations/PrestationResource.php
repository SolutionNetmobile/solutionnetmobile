<?php

namespace App\Filament\Resources\Prestations;

use App\Filament\Resources\Prestations\Pages\CreatePrestation;
use App\Filament\Resources\Prestations\Pages\EditPrestation;
use App\Filament\Resources\Prestations\Pages\ListPrestations;
use App\Filament\Resources\Prestations\Schemas\PrestationForm;
use App\Filament\Resources\Prestations\Tables\PrestationsTable;
use App\Models\Prestation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PrestationResource extends Resource
{
    protected static ?string $model = Prestation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus du site';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'prestation';

    protected static ?string $pluralModelLabel = 'prestations';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PrestationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrestationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrestations::route('/'),
            'create' => CreatePrestation::route('/create'),
            'edit' => EditPrestation::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
