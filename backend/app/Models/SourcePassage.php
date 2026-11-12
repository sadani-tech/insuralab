<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourcePassage extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['published' => 'boolean'];

    protected static function booted(): void
    {
        static::updating(function (SourcePassage $passage): void {
            if ($passage->isDirty(['key', 'source_id', 'version', 'locator', 'body']) &&
                (KnowledgeEntry::where('source_passage_id', $passage->id)->exists() || KnowledgeRelation::where('source_passage_id', $passage->id)->exists())) {
                throw \Illuminate\Validation\ValidationException::withMessages(['version' => 'Passage ini sudah dirujuk. Buat passage dengan key/versi baru; versi lama boleh ditarik dari publikasi.']);
            }
        });
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('published', true)->whereHas('source', fn ($q) => $q->where('access_level', 'public'));
    }
}
