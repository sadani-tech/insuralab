<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Quiz
 */
class QuizSummaryResource extends JsonResource
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
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'questions_count' => $this->whenNotNull($this->questions_count ?? null),
            'related_learning_paths' => $this->whenLoaded('learningPaths', fn () => $this->learningPaths->map(fn ($path) => [
                'title' => $path->title,
                'slug' => $path->slug,
            ])->values()),
        ];
    }
}
