<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KnowledgeEntry extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['published' => 'boolean', 'keywords' => 'array', 'steps' => 'array'];

    public function passage(): BelongsTo
    {
        return $this->belongsTo(SourcePassage::class, 'source_passage_id');
    }

    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(GlossaryTerm::class, 'knowledge_entry_term');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('published', true)->whereHas('passage', fn ($q) => $q->public());
    }
}
