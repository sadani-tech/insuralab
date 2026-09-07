<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductStructure: string implements HasLabel
{
    case Traditional = 'traditional';
    case Paydi = 'paydi';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Traditional => 'Traditional',
            self::Paydi => 'Unit link / PAYDI',
            self::Other => 'Other',
        };
    }
}
