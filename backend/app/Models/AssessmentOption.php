<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_question_id', 'key', 'label', 'weight',
        'protection_category_key', 'metadata', 'sort_order',
    ];

    protected $casts = [
        'weight' => 'integer',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }

    public function impacts(): HasMany
    {
        return $this->hasMany(AssessmentOptionImpact::class);
    }

    public function learningArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'assessment_option_articles')
            ->withPivot('sort_order')
            ->whereNull('articles.provider_id')
            ->public()
            ->orderByPivot('sort_order');
    }
}
