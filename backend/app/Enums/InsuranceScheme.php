<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InsuranceScheme: string implements HasLabel
{
    case Conventional = 'conventional';
    case Sharia = 'sharia';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
