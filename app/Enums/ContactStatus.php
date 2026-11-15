<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContactStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case ToContact = 'to_contact';
    case InProgress = 'in_progress';
    case QuoteSent = 'quote_sent';
    case Won = 'won';
    case Lost = 'lost';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Nouveau',
            self::ToContact => 'À contacter',
            self::InProgress => 'En cours',
            self::QuoteSent => 'Devis envoyé',
            self::Won => 'Gagné',
            self::Lost => 'Perdu',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'info',
            self::ToContact => 'warning',
            self::InProgress => 'primary',
            self::QuoteSent => 'gray',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }

    /**
     * Hexadecimal color used by dashboard charts.
     */
    public function chartColor(): string
    {
        return match ($this) {
            self::New => '#38BDF8',
            self::ToContact => '#F59E0B',
            self::InProgress => '#0755C8',
            self::QuoteSent => '#667085',
            self::Won => '#16A34A',
            self::Lost => '#DC2626',
        };
    }

    /**
     * Statuses considered as an open (not closed) opportunity.
     *
     * @return array<int, self>
     */
    public static function open(): array
    {
        return [self::ToContact, self::InProgress, self::QuoteSent];
    }
}
