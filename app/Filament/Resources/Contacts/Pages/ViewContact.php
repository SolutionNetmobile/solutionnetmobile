<?php

namespace App\Filament\Resources\Contacts\Pages;

use App\Filament\Resources\Contacts\ContactActions;
use App\Filament\Resources\Contacts\ContactResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContact extends ViewRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ContactActions::changeStatus(),
            EditAction::make(),
            ActionGroup::make([
                ContactActions::archive(),
                ContactActions::unarchive(),
                DeleteAction::make()->label('Mettre à la corbeille'),
                RestoreAction::make(),
                ContactActions::erase(),
            ])->label('Plus')->button()->color('gray'),
        ];
    }
}
