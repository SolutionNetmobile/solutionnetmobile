<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectType: string implements HasLabel
{
    case Consulting = 'consulting';
    case Support = 'support';
    case Website = 'website';
    case WebApplication = 'web_application';
    case MobileApplication = 'mobile_application';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Consulting => 'Conseil / stratégie digitale',
            self::Support => 'Accompagnement digital',
            self::Website => 'Site web',
            self::WebApplication => 'Application web',
            self::MobileApplication => 'Application mobile',
            self::Other => 'Autre',
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
