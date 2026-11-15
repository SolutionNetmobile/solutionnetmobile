<?php

namespace App\Filament\Resources\Methodologies\Pages;

use App\Filament\Resources\Methodologies\MethodologyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMethodology extends EditRecord
{
    protected static string $resource = MethodologyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
