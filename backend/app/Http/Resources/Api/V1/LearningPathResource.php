<?php

namespace App\Http\Resources\Api\V1;

use App\Models\LearningPath;
use App\Models\LearningPathItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An ordered curated journey through published articles and quizzes.
 *
 * @mixin LearningPath
 */
class LearningPathResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'level' => $this->level?->value,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'items' => $this->whenLoaded('items', fn () => $this->items
                ->filter(fn (LearningPathItem $item) => $item->item_type === 'article' ? $item->article !== null : $item->quiz !== null)
                ->values()
                ->map(fn (LearningPathItem $item, int $i) => [
                    'position' => $i + 1,
                    'type' => $item->item_type,
                    'note' => $item->note,
                    'article' => $item->item_type === 'article' ? new ArticleSummaryResource($item->article) : null,
                    'quiz' => $item->item_type === 'quiz' ? [
                        'id' => $item->quiz->id,
                        'title' => $item->quiz->title,
                        'slug' => $item->quiz->slug,
                        'description' => $item->quiz->description,
                        'level' => $item->quiz->level?->value,
                        'estimated_duration_minutes' => $item->quiz->estimated_duration_minutes,
                        'category' => $item->quiz->category ? [
                            'name' => $item->quiz->category->name,
                            'slug' => $item->quiz->category->slug,
                        ] : null,
                        'questions_count' => $item->quiz->questions_count,
                        'related_learning_paths' => [],
                    ] : null,
                ])),
            'lessons_count' => $this->whenLoaded('items', fn () => $this->items->where('item_type', 'article')->filter(fn ($item) => $item->article !== null)->count()),
            'quizzes_count' => $this->whenLoaded('items', fn () => $this->items->where('item_type', 'quiz')->filter(fn ($item) => $item->quiz !== null)->count()),
        ];
    }
}
