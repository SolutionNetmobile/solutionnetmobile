<?php

namespace App\Filament\Resources\Contacts\RelationManagers;

use App\Enums\ContactNoteType;
use App\Models\ContactNote;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Internal notes & exchange history of a prospect (back office only).
 */
class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Historique des échanges & notes internes';

    protected static ?string $modelLabel = 'note';

    protected static ?string $pluralModelLabel = 'notes';

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->notes()->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Type d’échange')
                ->options(ContactNoteType::manualOptions())
                ->default(ContactNoteType::Note->value)
                ->required()
                ->native(false),
            Textarea::make('body')
                ->label('Contenu')
                ->required()
                ->rows(5)
                ->maxLength(5000)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('type')->label('Type')->badge(),
                TextColumn::make('body')->label('Contenu')->wrap()->searchable(),
                TextColumn::make('author.name')->label('Auteur')->placeholder('Système'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Type')->options(ContactNoteType::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un échange / une note')
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()->visible(fn (ContactNote $record): bool => $record->type !== ContactNoteType::StatusChange),
                DeleteAction::make()->visible(fn (ContactNote $record): bool => $record->type !== ContactNoteType::StatusChange),
            ])
            ->emptyStateHeading('Aucun échange pour le moment')
            ->emptyStateDescription('Consignez ici vos appels, e-mails, rendez-vous et notes internes.');
    }
}
