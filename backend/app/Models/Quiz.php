<?php

namespace App\Models;

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;
    use HasSlug;

    protected $slugSourceColumn = 'title';

    protected $fillable = [
        'category_id', 'title', 'slug', 'description', 'level', 'estimated_duration_minutes', 'status',
    ];

    protected $casts = [
        'level' => ContentLevel::class,
        'status' => ContentStatus::class,
        'estimated_duration_minutes' => 'integer',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function learningPaths(): BelongsToMany
    {
        return $this->belongsToMany(LearningPath::class, 'learning_path_items', 'quiz_id', 'learning_path_id')
            ->wherePivot('item_type', 'quiz')
            ->withPivot(['note', 'position'])
            ->withTimestamps();
    }
}
