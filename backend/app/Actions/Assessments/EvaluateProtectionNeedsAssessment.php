<?php

namespace App\Actions\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentDimension;
use App\Models\AssessmentOption;
use App\Models\AssessmentQuestion;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class EvaluateProtectionNeedsAssessment
{
    /**
     * Evaluate anonymous answers using only database-configured impacts.
     *
     * @param  array<int, array{question: string, option: string}>  $answers
     * @return array<string, mixed>
     */
    public function handle(Assessment $assessment, array $answers): array
    {
        $assessment->loadMissing([
            'questions.options.impacts.dimension',
            'questions.options.learningArticles.category',
            'dimensions.learningArticles.category',
            'dimensions.learningPath',
        ]);

        $selected = $this->validateAndResolveAnswers($assessment, $answers);
        $applicableQuestions = $assessment->questions
            ->filter(fn (AssessmentQuestion $question) => $this->isApplicable($question, $selected));

        $dimensions = $assessment->dimensions->map(function (AssessmentDimension $dimension) use ($assessment, $applicableQuestions, $selected): array {
            $earned = 0;
            $maximum = 0;
            $reasons = collect();

            foreach ($applicableQuestions as $question) {
                $maximum += (int) $question->options
                    ->flatMap->impacts
                    ->where('assessment_dimension_id', $dimension->id)
                    ->max('weight');

                /** @var AssessmentOption|null $option */
                $option = $selected->get($question->key);
                if (! $option) {
                    continue;
                }

                $impact = $option->impacts->firstWhere('assessment_dimension_id', $dimension->id);
                if ($impact) {
                    $earned += $impact->weight;
                    if ($impact->reason) {
                        $reasons->push($impact->reason);
                    }
                }
            }

            $normalized = $maximum > 0 ? $earned / $maximum : 0.0;
            [$relevance, $label, $rank] = $this->relevanceFor(
                $normalized,
                $assessment->rules['thresholds'] ?? [],
            );

            return [
                'key' => $dimension->key,
                'name' => $dimension->name,
                'relevance' => $relevance,
                'relevance_label' => $label,
                'description' => $dimension->description,
                'reasons' => $reasons->isNotEmpty()
                    ? $reasons->unique()->values()->all()
                    : ['Tidak ada jawaban yang secara khusus meningkatkan relevansi topik ini; topik ini tetap tersedia sebagai fondasi pembelajaran.'],
                'learning_topics' => $dimension->learningArticles->map(fn ($article) => [
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'url' => '/learn/'.$article->slug,
                ])->values()->all(),
                'learning_path' => $dimension->learningPath ? [
                    'title' => $dimension->learningPath->title,
                    'slug' => $dimension->learningPath->slug,
                    'url' => '/learn/path/'.$dimension->learningPath->slug,
                ] : null,
                '_normalized' => $normalized,
                '_rank' => $rank,
                '_sort_order' => $dimension->sort_order,
            ];
        })->sort(function (array $a, array $b): int {
            return [$b['_rank'], $b['_normalized'], -$b['_sort_order']]
                <=> [$a['_rank'], $a['_normalized'], -$a['_sort_order']];
        })->values()->map(function (array $dimension): array {
            unset($dimension['_normalized'], $dimension['_rank'], $dimension['_sort_order']);

            return $dimension;
        });

        $additionalArticles = $selected->flatMap->learningArticles
            ->unique('id')
            ->values()
            ->map(fn ($article) => [
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'url' => '/learn/'.$article->slug,
            ]);

        $result = [
            'assessment' => $assessment->slug,
            'methodology_version' => $assessment->methodology_version,
            'dimensions' => $dimensions->all(),
            'disclaimer' => $assessment->disclaimer,
        ];

        if ($additionalArticles->isNotEmpty()) {
            $result['additional_learning'] = [
                'title' => $assessment->rules['additional_learning_title'] ?? 'Sudah punya perlindungan?',
                'description' => $assessment->rules['additional_learning_description'] ?? 'Memahami perlindungan yang sudah dimiliki membantu kamu mengenali manfaat, batasan, dan prosesnya.',
                'topics' => $additionalArticles->all(),
            ];
        }

        return $result;
    }

    /**
     * @param  array<int, array{question: string, option: string}>  $answers
     * @return Collection<string, AssessmentOption>
     */
    private function validateAndResolveAnswers(Assessment $assessment, array $answers): Collection
    {
        $keys = collect($answers)->pluck('question');
        if ($keys->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['answers' => 'Setiap pertanyaan hanya boleh memiliki satu jawaban.']);
        }

        $questions = $assessment->questions->keyBy('key');
        $selected = collect();

        foreach ($answers as $index => $answer) {
            /** @var AssessmentQuestion|null $question */
            $question = $questions->get($answer['question']);
            if (! $question) {
                throw ValidationException::withMessages(["answers.$index.question" => 'Pertanyaan tidak termasuk dalam assessment ini.']);
            }

            /** @var AssessmentOption|null $option */
            $option = $question->options->firstWhere('key', $answer['option']);
            if (! $option) {
                throw ValidationException::withMessages(["answers.$index.option" => 'Pilihan tidak sesuai dengan pertanyaan tersebut.']);
            }

            $selected->put($question->key, $option);
        }

        foreach ($assessment->questions as $question) {
            $applicable = $this->isApplicable($question, $selected);
            if (! $applicable && $selected->has($question->key)) {
                throw ValidationException::withMessages(['answers' => 'Jawaban memuat pertanyaan yang tidak berlaku untuk situasi yang dipilih.']);
            }
            if ($applicable && $question->is_required && ! $selected->has($question->key)) {
                throw ValidationException::withMessages(['answers' => "Jawaban untuk pertanyaan '{$question->key}' belum dipilih."]);
            }
        }

        return $selected;
    }

    /** @param Collection<string, AssessmentOption> $selected */
    private function isApplicable(AssessmentQuestion $question, Collection $selected): bool
    {
        if (! $question->depends_on_question_key) {
            return true;
        }

        $dependency = $selected->get($question->depends_on_question_key);

        return $dependency !== null
            && in_array($dependency->key, $question->applicable_option_keys ?? [], true);
    }

    /** @return array{string, string, int} */
    private function relevanceFor(float $normalized, array $thresholds): array
    {
        $high = (float) ($thresholds['highly_relevant'] ?? 0.60);
        $explore = (float) ($thresholds['worth_exploring'] ?? 0.30);

        if ($normalized >= $high) {
            return ['highly_relevant', 'Sangat Relevan', 2];
        }
        if ($normalized >= $explore) {
            return ['worth_exploring', 'Layak Dieksplorasi', 1];
        }

        return ['foundational', 'Fondasi Pembelajaran', 0];
    }
}
