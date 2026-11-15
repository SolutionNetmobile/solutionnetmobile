<?php

namespace App\Filament\Resources\Methodologies;

use App\Filament\Resources\Methodologies\Pages\CreateMethodology;
use App\Filament\Resources\Methodologies\Pages\EditMethodology;
use App\Filament\Resources\Methodologies\Pages\ListMethodologies;
use App\Filament\Resources\Methodologies\Schemas\MethodologyForm;
use App\Filament\Resources\Methodologies\Tables\MethodologiesTable;
use App\Models\Methodology;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MethodologyResource extends Resource
{
    protected static ?string $model = Methodology::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus du site';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Méthodologie';

    protected static ?string $modelLabel = 'étape';

    protected static ?string $pluralModelLabel = 'étapes de la méthodologie';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return MethodologyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MethodologiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMethodologies::route('/'),
            'create' => CreateMethodology::route('/create'),
            'edit' => EditMethodology::route('/{record}/edit'),
        ];
    }
}
