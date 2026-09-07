<?php

namespace App\Models;

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    use HasFactory;
    use HasSlug;

    protected $slugSourceColumn = 'title';

    protected $fillable = [
        'title', 'slug', 'description', 'level', 'estimated_duration_minutes', 'status', 'sort_order',
    ];

    protected $casts = [
        'level' => ContentLevel::class,
        'status' => ContentStatus::class,
        'sort_order' => 'integer',
        'estimated_duration_minutes' => 'integer',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LearningPathItem::class)->orderBy('position');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'learning_path_items')
            ->withPivot(['note', 'position'])
            ->withTimestamps()
            ->orderBy('learning_path_items.position');
    }

    public function publicArticleItems(): HasMany
    {
        return $this->items()->where('item_type', 'article')->whereHas('article', fn (Builder $query) => $query->public());
    }

    public function publicQuizItems(): HasMany
    {
        return $this->items()->where('item_type', 'quiz')->whereHas('quiz', fn (Builder $query) => $query->published());
    }
}
