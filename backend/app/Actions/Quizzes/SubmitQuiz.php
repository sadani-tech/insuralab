<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SubmitQuiz
{
    /**
     * @param  array<int, array{question: string, option: string}>  $answers
     * @return array<string, mixed>
     */
    public function handle(Quiz $quiz, array $answers): array
    {
        $quiz->loadMissing([
            'questions.options',
            'questions.relatedArticles' => fn ($query) => $query->general()->public(),
        ]);

        $selected = $this->validateAndResolveAnswers($quiz, $answers);
        $correctCount = 0;
        $reviewTopics = collect();

        $results = $quiz->questions->map(function (QuizQuestion $question) use ($selected, &$correctCount, $reviewTopics): array {
            /** @var QuizOption $selectedOption */
            $selectedOption = $selected->get($question->key);
            /** @var QuizOption|null $correctOption */
            $correctOption = $question->options->firstWhere('is_correct', true);

            if (! $correctOption) {
                throw ValidationException::withMessages([
                    'quiz' => "Pertanyaan '{$question->key}' belum memiliki jawaban benar yang valid.",
                ]);
            }

            $isCorrect = $selectedOption->id === $correctOption->id;
            if ($isCorrect) {
                $correctCount++;
            } else {
                $reviewTopics->push(...$question->relatedArticles);
            }

            return [
                'question' => $question->key,
                'prompt' => $question->prompt,
                'correct' => $isCorrect,
                'selected_option' => $selectedOption->key,
                'selected_option_label' => $selectedOption->label,
                'correct_option' => $correctOption->key,
                'correct_option_label' => $correctOption->label,
                'explanation' => $selectedOption->explanation ?: $question->explanation,
                'concept_explanation' => $question->explanation,
                'related_lessons' => $question->relatedArticles->map(fn ($article) => $this->learningTopic($article))->values()->all(),
            ];
        })->values();

        $total = $quiz->questions->count();

        return [
            'quiz' => [
                'slug' => $quiz->slug,
                'title' => $quiz->title,
            ],
            'score' => [
                'correct' => $correctCount,
                'total' => $total,
                'percentage' => $total > 0 ? (int) round(($correctCount / $total) * 100) : 0,
            ],
            'results' => $results->all(),
            'review_topics' => $reviewTopics
                ->unique('id')
                ->values()
                ->map(fn ($article) => $this->learningTopic($article))
                ->all(),
        ];
    }

    /**
     * @param  array<int, array{question: string, option: string}>  $answers
     * @return Collection<string, QuizOption>
     */
    private function validateAndResolveAnswers(Quiz $quiz, array $answers): Collection
    {
        $answerKeys = collect($answers)->pluck('question');
        if ($answerKeys->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['answers' => 'Setiap pertanyaan hanya boleh memiliki satu jawaban.']);
        }

        $questions = $quiz->questions->keyBy('key');
        $selected = collect();

        foreach ($answers as $index => $answer) {
            /** @var QuizQuestion|null $question */
            $question = $questions->get($answer['question']);
            if (! $question) {
                throw ValidationException::withMessages(["answers.$index.question" => 'Pertanyaan tidak termasuk dalam quiz ini.']);
            }

            /** @var QuizOption|null $option */
            $option = $question->options->firstWhere('key', $answer['option']);
            if (! $option) {
                throw ValidationException::withMessages(["answers.$index.option" => 'Pilihan tidak sesuai dengan pertanyaan tersebut.']);
            }

            $selected->put($question->key, $option);
        }

        foreach ($questions as $question) {
            if (! $selected->has($question->key)) {
                throw ValidationException::withMessages(['answers' => "Jawaban untuk pertanyaan '{$question->key}' belum dipilih."]);
            }
        }

        return $selected;
    }

    /** @return array{title: string, slug: string, excerpt: ?string, url: string} */
    private function learningTopic($article): array
    {
        return [
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'url' => '/learn/'.$article->slug,
        ];
    }
}
