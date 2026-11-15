<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Back-office roles. Their permissions are defined in App\Support\Permissions.
 */
enum Role: string implements HasColor, HasLabel
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Editor = 'editor';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super administrateur',
            self::Admin => 'Administrateur',
            self::Editor => 'Éditeur',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Admin => 'primary',
            self::Editor => 'gray',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labelFor(string $value): string
    {
        return self::tryFrom($value)?->getLabel() ?? $value;
    }
}
