<?php

namespace App\Http\Resources\Api\V1;

use App\Models\LearningPath;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LearningPath
 */
class LearningPathSummaryResource extends JsonResource
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
            'lessons_count' => $this->whenNotNull($this->lessons_count ?? null),
            'quizzes_count' => $this->whenNotNull($this->quizzes_count ?? null),
        ];
    }
}
