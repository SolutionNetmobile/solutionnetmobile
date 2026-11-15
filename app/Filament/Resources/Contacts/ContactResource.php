<?php

namespace App\Filament\Resources\Contacts;

use App\Enums\ContactStatus;
use App\Filament\Resources\Contacts\Pages\EditContact;
use App\Filament\Resources\Contacts\Pages\ListContacts;
use App\Filament\Resources\Contacts\Pages\ViewContact;
use App\Filament\Resources\Contacts\RelationManagers\NotesRelationManager;
use App\Filament\Resources\Contacts\Schemas\ContactForm;
use App\Filament\Resources\Contacts\Schemas\ContactInfolist;
use App\Filament\Resources\Contacts\Tables\ContactsTable;
use App\Models\Contact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Prospects';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Demandes & prospects';

    protected static ?string $modelLabel = 'prospect';

    protected static ?string $pluralModelLabel = 'prospects';

    protected static ?string $recordTitleAttribute = 'last_name';

    public static function form(Schema $schema): Schema
    {
        return ContactForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
            'view' => ViewContact::route('/{record}'),
            'edit' => EditContact::route('/{record}/edit'),
        ];
    }

    public static function getRecordTitle(?Model $record): string
    {
        /** @var Contact|null $record */
        return $record ? $record->full_name.($record->company ? ' — '.$record->company : '') : 'Prospect';
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['last_name', 'first_name', 'company', 'email'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Contact::query()->notArchived()->where('status', ContactStatus::New)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Nouvelles demandes à traiter';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
