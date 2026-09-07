<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentDimension extends Model
{
    use HasFactory;

    protected $fillable = ['assessment_id', 'learning_path_id', 'key', 'name', 'description', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function impacts(): HasMany
    {
        return $this->hasMany(AssessmentOptionImpact::class);
    }

    public function learningArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'assessment_dimension_articles')
            ->withPivot('sort_order')
            ->whereNull('articles.provider_id')
            ->public()
            ->orderByPivot('sort_order');
    }
}
