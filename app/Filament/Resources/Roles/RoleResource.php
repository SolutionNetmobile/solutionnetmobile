<?php

namespace App\Filament\Resources\Roles;

use App\Enums\Role as RoleEnum;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Support\Permissions;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use UnitEnum;

/**
 * Edit which permissions each back-office role holds (super administrator only).
 */
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Rôles & permissions';

    protected static ?string $modelLabel = 'rôle';

    protected static ?string $pluralModelLabel = 'rôles';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('roles.manage') ?? false;
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return static::canAccess() && $record->getAttribute('name') !== RoleEnum::SuperAdmin->value;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getRecordTitle(?Model $record): string
    {
        return RoleEnum::labelFor((string) $record?->getAttribute('name'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Permissions')
                ->columnSpanFull()
                ->schema([
                    Text::make('Le super administrateur dispose toujours de toutes les permissions.'),
                    CheckboxList::make('permissions')
                        ->hiddenLabel()
                        ->relationship('permissions', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Permission $record): string => Permissions::label($record->name))
                        ->columns(['md' => 2, 'xl' => 4])
                        ->gridDirection('row')
                        ->bulkToggleable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Rôle')
                    ->formatStateUsing(fn (string $state): string => RoleEnum::labelFor($state))
                    ->badge()
                    ->color(fn (string $state): string => RoleEnum::tryFrom($state)?->getColor() ?? 'gray'),
                TextColumn::make('permissions_count')->label('Permissions')->counts('permissions'),
                TextColumn::make('users_count')->label('Utilisateurs')->counts('users'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
