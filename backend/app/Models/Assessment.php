<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * Assessment DEFINITION only. There is no evaluation engine in Phase 2
 * (ROADMAP §7). By design an assessment can only ever map answers to LEARNING
 * TOPICS / protection categories — never to "buy product X".
 */
class Assessment extends Model
{
    use HasFactory;
    use HasSlug;

    protected $slugSourceColumn = 'title';

    protected $fillable = [
        'title', 'slug', 'description', 'intro', 'disclaimer', 'status', 'methodology_version', 'rules',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'rules' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $assessment): void {
            $thresholds = $assessment->rules['thresholds'] ?? null;
            if (! is_array($thresholds)) {
                return;
            }

            $explore = (float) ($thresholds['worth_exploring'] ?? -1);
            $high = (float) ($thresholds['highly_relevant'] ?? -1);
            if ($explore < 0 || $high > 1 || $explore >= $high) {
                throw ValidationException::withMessages([
                    'rules.thresholds' => 'Ambang relevansi harus berurutan dan berada pada rentang 0 sampai 1.',
                ]);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('sort_order');
    }

    public function dimensions(): HasMany
    {
        return $this->hasMany(AssessmentDimension::class)->orderBy('sort_order');
    }
}
