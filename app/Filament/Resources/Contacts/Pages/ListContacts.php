<?php

namespace App\Filament\Resources\Contacts\Pages;

use App\Enums\ContactStatus;
use App\Filament\Resources\Contacts\ContactActions;
use App\Filament\Resources\Contacts\ContactResource;
use App\Models\Contact;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ContactActions::exportAll(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $active = fn (): Builder => Contact::query()->notArchived();

        return [
            'active' => Tab::make('En cours de traitement')
                ->icon('heroicon-o-inbox-stack')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->notArchived())
                ->badge(fn (): int => $active()->count()),
            'new' => Tab::make('Nouveaux')
                ->icon('heroicon-o-sparkles')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->notArchived()->where('status', ContactStatus::New))
                ->badge(fn (): int => $active()->where('status', ContactStatus::New)->count())
                ->badgeColor('info'),
            'open' => Tab::make('Opportunités ouvertes')
                ->icon('heroicon-o-briefcase')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->notArchived()->whereIn('status', ContactStatus::open())),
            'closed' => Tab::make('Gagnés / perdus')
                ->icon('heroicon-o-flag')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->notArchived()->whereIn('status', [ContactStatus::Won, ContactStatus::Lost])),
            'archived' => Tab::make('Archivés')
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->archived()),
        ];
    }
}
