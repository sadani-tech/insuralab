<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LearningPathResource;
use App\Http\Resources\Api\V1\LearningPathSummaryResource;
use App\Models\LearningPath;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Curated ordered journeys through published articles and quizzes. Anonymous
 * progress stays in the browser; Laravel only serves the editorial sequence.
 */
class LearningPathController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $paths = LearningPath::query()
            ->published()
            ->withCount(['publicArticleItems as lessons_count', 'publicQuizItems as quizzes_count'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return LearningPathSummaryResource::collection($paths);
    }

    public function show(string $slug): LearningPathResource
    {
        $path = LearningPath::query()
            ->published()
            ->with([
                'items.article' => fn ($query) => $query->public(),
                'items.article.category:id,name,slug',
                'items.article.provider:id,name,slug,country_of_origin,last_reviewed_at',
                'items.quiz' => fn ($query) => $query->published()->withCount('questions'),
                'items.quiz.category:id,name,slug',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return new LearningPathResource($path);
    }
}
