<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Quizzes\SubmitQuiz;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SubmitQuizRequest;
use App\Http\Resources\Api\V1\QuizResource;
use App\Http\Resources\Api\V1\QuizSummaryResource;
use App\Models\Quiz;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public quiz definitions and stateless server-owned scoring. Definition
 * payloads never contain `is_correct` or explanations.
 */
class QuizController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $quizzes = Quiz::query()
            ->published()
            ->with(['category:id,name,slug', 'learningPaths' => fn ($query) => $query->published()->orderBy('sort_order')])
            ->withCount('questions')
            ->orderBy('title')
            ->get();

        return QuizSummaryResource::collection($quizzes);
    }

    public function show(string $slug): QuizResource
    {
        $quiz = Quiz::query()
            ->published()
            ->with([
                'category:id,name,slug',
                'learningPaths' => fn ($query) => $query->published()->orderBy('sort_order'),
                'questions' => fn ($query) => $query->orderBy('sort_order'),
                'questions.options' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return new QuizResource($quiz);
    }

    public function submit(SubmitQuizRequest $request, string $slug, SubmitQuiz $submitQuiz): array
    {
        $quiz = Quiz::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return ['data' => $submitQuiz->handle($quiz, $request->validated('answers'))];
    }
}
