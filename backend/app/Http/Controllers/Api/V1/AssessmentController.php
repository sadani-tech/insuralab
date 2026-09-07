<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Assessments\EvaluateProtectionNeedsAssessment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EvaluateAssessmentRequest;
use App\Http\Resources\Api\V1\AssessmentResource;
use App\Http\Resources\Api\V1\AssessmentSummaryResource;
use App\Models\Assessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

/** Public definitions plus stateless educational evaluation. */
class AssessmentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $assessments = Assessment::query()
            ->published()
            ->withCount('questions')
            ->orderBy('title')
            ->get();

        return AssessmentSummaryResource::collection($assessments);
    }

    public function show(string $slug): AssessmentResource
    {
        $assessment = Assessment::query()
            ->published()
            ->with(['questions' => fn ($q) => $q->orderBy('sort_order'), 'questions.options' => fn ($q) => $q->orderBy('sort_order')])
            ->where('slug', $slug)
            ->firstOrFail();

        return new AssessmentResource($assessment);
    }

    public function evaluate(
        EvaluateAssessmentRequest $request,
        string $slug,
        EvaluateProtectionNeedsAssessment $evaluator,
    ): JsonResponse {
        $assessment = Assessment::query()->published()->where('slug', $slug)->firstOrFail();

        if ($request->validated('methodology_version') !== $assessment->methodology_version) {
            throw ValidationException::withMessages([
                'methodology_version' => 'Assessment telah diperbarui. Muat ulang sebelum melanjutkan.',
            ]);
        }

        return response()->json([
            'data' => $evaluator->handle($assessment, $request->validated('answers')),
        ]);
    }
}
