<?php

namespace App\Filament\Resources\Contacts;

use App\Enums\ContactNoteType;
use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Services\ContactExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Prospect actions shared by the table and the detail page.
 */
final class ContactActions
{
    public static function changeStatus(): Action
    {
        return Action::make('changeStatus')
            ->label('Changer le statut')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->authorize('update')
            ->fillForm(fn (Contact $record): array => ['status' => $record->status])
            ->schema([
                Select::make('status')
                    ->label('Nouveau statut')
                    ->options(ContactStatus::class)
                    ->required()
                    ->native(false),
                Textarea::make('comment')
                    ->label('Commentaire (facultatif)')
                    ->rows(3)
                    ->maxLength(2000),
            ])
            ->action(function (Contact $record, array $data): void {
                $record->update(['status' => $data['status']]);

                if (filled($data['comment'] ?? null)) {
                    $record->notes()->create([
                        'user_id' => auth()->id(),
                        'type' => ContactNoteType::Note,
                        'body' => $data['comment'],
                    ]);
                }

                Notification::make()->title('Statut mis à jour')->success()->send();
            });
    }

    public static function archive(): Action
    {
        return Action::make('archive')
            ->label('Archiver')
            ->icon('heroicon-o-archive-box')
            ->color('gray')
            ->authorize('update')
            ->requiresConfirmation()
            ->modalDescription('La demande sera retirée des listes actives mais restera consultable dans l’onglet « Archivés ».')
            ->visible(fn (Contact $record): bool => ! $record->isArchived() && ! $record->trashed())
            ->action(function (Contact $record): void {
                $record->update(['archived_at' => now()]);
                Notification::make()->title('Demande archivée')->success()->send();
            });
    }

    public static function unarchive(): Action
    {
        return Action::make('unarchive')
            ->label('Désarchiver')
            ->icon('heroicon-o-archive-box-x-mark')
            ->color('gray')
            ->authorize('update')
            ->visible(fn (Contact $record): bool => $record->isArchived() && ! $record->trashed())
            ->action(function (Contact $record): void {
                $record->update(['archived_at' => null]);
                Notification::make()->title('Demande restaurée dans les listes actives')->success()->send();
            });
    }

    /**
     * GDPR right to erasure: permanently removes the prospect and its history.
     */
    public static function erase(): Action
    {
        return Action::make('erase')
            ->label('Effacer (RGPD)')
            ->icon('heroicon-o-shield-exclamation')
            ->color('danger')
            ->authorize('forceDelete')
            ->requiresConfirmation()
            ->modalHeading('Effacer définitivement les données ?')
            ->modalDescription('À utiliser pour répondre à une demande de suppression des données personnelles : la demande et tout son historique seront supprimés de façon irréversible.')
            ->modalSubmitActionLabel('Effacer définitivement')
            ->action(function (Contact $record, Action $action): void {
                $record->forceDelete();
                Notification::make()->title('Données du prospect effacées')->success()->send();
                $action->redirect(ContactResource::getUrl('index'));
            });
    }

    public static function exportAll(): Action
    {
        return Action::make('export')
            ->label('Exporter en CSV')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->visible(fn (): bool => auth()->user()?->can('export', Contact::class) ?? false)
            ->action(fn (): StreamedResponse => app(ContactExporter::class)->download(Contact::query()));
    }

    public static function exportSelected(): BulkAction
    {
        return BulkAction::make('exportSelected')
            ->label('Exporter la sélection (CSV)')
            ->icon('heroicon-o-arrow-down-tray')
            ->visible(fn (): bool => auth()->user()?->can('export', Contact::class) ?? false)
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records): StreamedResponse => app(ContactExporter::class)->download(
                Contact::query()->withTrashed()->whereKey($records->modelKeys())
            ));
    }

    public static function archiveSelected(): BulkAction
    {
        return BulkAction::make('archiveSelected')
            ->label('Archiver la sélection')
            ->icon('heroicon-o-archive-box')
            ->authorize('deleteAny')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                Contact::query()->whereKey($records->modelKeys())->update(['archived_at' => now()]);
                Notification::make()->title('Demandes archivées')->success()->send();
            });
    }
}
