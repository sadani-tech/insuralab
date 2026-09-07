<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Assessment;
use App\Models\AssessmentOption;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public questionnaire definition. Internal impacts, reasons, weights, and
 * thresholds are deliberately excluded.
 *
 * @mixin Assessment
 */
class AssessmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'methodology_version' => $this->methodology_version,
            'description' => $this->description,
            'intro' => $this->intro,
            'disclaimer' => $this->disclaimer,
            'questions' => $this->whenLoaded('questions', fn () => $this->questions->map(fn (AssessmentQuestion $question) => [
                'id' => $question->id,
                'key' => $question->key,
                'prompt' => $question->prompt,
                'help_text' => $question->help_text,
                'type' => $question->type,
                'is_required' => (bool) $question->is_required,
                'condition' => $question->depends_on_question_key ? [
                    'question' => $question->depends_on_question_key,
                    'options' => $question->applicable_option_keys ?? [],
                ] : null,
                'options' => $question->options->map(fn (AssessmentOption $option) => [
                    'id' => $option->id,
                    'key' => $option->key,
                    'label' => $option->label,
                ])->values(),
            ])->values()),
        ];
    }
}
