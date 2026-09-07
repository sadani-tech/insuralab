<?php

namespace App\Models\Concerns;

use App\Models\Source;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Attaches the reusable `sources` records to any model via the polymorphic
 * `sourceables` pivot (ADR-0004).
 */
trait HasSources
{
    public function sources(): MorphToMany
    {
        return $this->morphToMany(Source::class, 'sourceable')
            ->withPivot('note')
            ->withTimestamps();
    }
}
