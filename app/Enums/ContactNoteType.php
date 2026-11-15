<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ContactNoteType: string implements HasColor, HasIcon, HasLabel
{
    case Note = 'note';
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case StatusChange = 'status_change';

    public function getLabel(): string
    {
        return match ($this) {
            self::Note => 'Note interne',
            self::Call => 'Appel',
            self::Email => 'E-mail',
            self::Meeting => 'Rendez-vous',
            self::StatusChange => 'Changement de statut',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Note => 'gray',
            self::Call => 'info',
            self::Email => 'primary',
            self::Meeting => 'success',
            self::StatusChange => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Note => 'heroicon-o-pencil-square',
            self::Call => 'heroicon-o-phone',
            self::Email => 'heroicon-o-envelope',
            self::Meeting => 'heroicon-o-calendar-days',
            self::StatusChange => 'heroicon-o-arrow-path',
        };
    }

    /**
     * Types an administrator can create manually (status changes are logged automatically).
     *
     * @return array<string, string>
     */
    public static function manualOptions(): array
    {
        return collect(self::cases())
            ->reject(fn (self $case): bool => $case === self::StatusChange)
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->getLabel()])
            ->all();
    }
}
