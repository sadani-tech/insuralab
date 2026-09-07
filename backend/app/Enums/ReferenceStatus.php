<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Trust state of provider-specific / dynamic content (ADR-0004).
 *
 * - current:      re-verified against a current official document
 * - historical:   tied to a past period / superseded scheme — never shown as current
 * - needs_review: not yet verified, or the source is inconsistent/ambiguous
 */
enum ReferenceStatus: string implements HasColor, HasLabel
{
    case Current = 'current';
    case Historical = 'historical';
    case NeedsReview = 'needs_review';

    public function getLabel(): string
    {
        return match ($this) {
            self::Current => 'Current',
            self::Historical => 'Historical',
            self::NeedsReview => 'Needs review',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Current => 'success',
            self::Historical => 'warning',
            self::NeedsReview => 'danger',
        };
    }
}
