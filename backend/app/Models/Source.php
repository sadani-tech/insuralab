<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Reusable citation. One source (e.g. the Allianz Basic Training PDF) attaches to
 * many entities through the polymorphic `sourceables` pivot (ADR-0004).
 * `notes` is where source inconsistencies are recorded — visibly, never resolved.
 */
class Source extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'publisher', 'url',
        'published_at', 'accessed_at', 'reference_period', 'notes', 'access_level',
    ];

    protected $casts = [
        'published_at' => 'date',
        'accessed_at' => 'date',
    ];

    public function passages(): HasMany
    {
        return $this->hasMany(SourcePassage::class);
    }

    public function articles(): MorphToMany
    {
        return $this->morphedByMany(Article::class, 'sourceable')->withPivot('note')->withTimestamps();
    }

    public function providers(): MorphToMany
    {
        return $this->morphedByMany(Provider::class, 'sourceable')->withPivot('note')->withTimestamps();
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'sourceable')->withPivot('note')->withTimestamps();
    }

    public function glossaryTerms(): MorphToMany
    {
        return $this->morphedByMany(GlossaryTerm::class, 'sourceable')->withPivot('note')->withTimestamps();
    }
}
