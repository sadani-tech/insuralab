<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id', 'key', 'prompt', 'help_text', 'type', 'is_required', 'sort_order',
        'depends_on_question_key', 'applicable_option_keys',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'applicable_option_keys' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $question): void {
            if (! $question->depends_on_question_key) {
                return;
            }

            $dependency = self::query()
                ->where('assessment_id', $question->assessment_id)
                ->where('key', $question->depends_on_question_key)
                ->first();
            $applicableKeys = $question->applicable_option_keys ?? [];
            $validKeys = $dependency?->options()->pluck('key')->all() ?? [];

            if (! $dependency || $dependency->id === $question->id || $applicableKeys === [] || array_diff($applicableKeys, $validKeys) !== []) {
                throw ValidationException::withMessages([
                    'depends_on_question_key' => 'Kondisi harus merujuk ke pertanyaan dan pilihan dalam assessment yang sama.',
                ]);
            }
        });
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(AssessmentOption::class)->orderBy('sort_order');
    }
}
