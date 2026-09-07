<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Quiz DEFINITION for the public API.
 *
 * SECURITY: this payload MUST NOT contain `is_correct` or explanations. The
 * submission endpoint evaluates semantic keys on the server.
 *
 * @mixin Quiz
 */
class QuizResource extends JsonResource
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
            'related_learning_paths' => $this->whenLoaded('learningPaths', fn () => $this->learningPaths->map(fn ($path) => [
                'title' => $path->title,
                'slug' => $path->slug,
            ])->values()),
            'questions' => $this->whenLoaded('questions', fn () => $this->questions->map(fn (QuizQuestion $question) => [
                'id' => $question->id,
                'key' => $question->key,
                'prompt' => $question->prompt,
                'type' => $question->type,
                'options' => $question->options->map(fn (QuizOption $option) => [
                    'id' => $option->id,
                    'key' => $option->key,
                    'label' => $option->label,
                ])->values(),
            ])->values()),
        ];
    }
}
