<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContentLevel: string implements HasLabel
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
