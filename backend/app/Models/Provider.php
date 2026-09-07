<?php

namespace App\Models;

use App\Enums\ProviderStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSources;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A provider is DATA, never schema (ADR-0002). Allianz is a row here — there is
 * no `allianz_*` table anywhere in this codebase.
 */
class Provider extends Model
{
    use HasFactory;
    use HasSlug;
    use HasSources;

    protected $fillable = [
        'name', 'slug', 'description', 'country_of_origin',
        'presence_in_indonesia', 'website_url', 'status',
        'last_reviewed_at', 'metadata',
    ];

    protected $casts = [
        'status' => ProviderStatus::class,
        'last_reviewed_at' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Providers suitable for public consumption: currently only `active` ones.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('status', ProviderStatus::Active->value);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Provider-specific educational articles (provider_id = this). */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function glossaryTerms(): HasMany
    {
        return $this->hasMany(GlossaryTerm::class);
    }
}
