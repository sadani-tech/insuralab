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
    /** Revoking a source hides dependent public content, not merely its citation. */
    public function scopeWithPublicEvidence(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereDoesntHave('sources', fn ($q) => $q->where('access_level', '!=', 'public'));
    }

    public function sources(): MorphToMany
    {
        return $this->morphToMany(Source::class, 'sourceable')
            ->withPivot('note')
            ->withTimestamps();
    }
}
