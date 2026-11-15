<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Budget: string implements HasLabel
{
    case LessThan5k = 'lt_5k';
    case From5kTo15k = '5k_15k';
    case From15kTo30k = '15k_30k';
    case MoreThan30k = 'gt_30k';
    case Undefined = 'undefined';

    public function getLabel(): string
    {
        return match ($this) {
            self::LessThan5k => 'Moins de 5 000 €',
            self::From5kTo15k => 'De 5 000 € à 15 000 €',
            self::From15kTo30k => 'De 15 000 € à 30 000 €',
            self::MoreThan30k => 'Plus de 30 000 €',
            self::Undefined => 'Je ne sais pas encore',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case): array => [$case->value => $case->getLabel()])->all();
    }
}
