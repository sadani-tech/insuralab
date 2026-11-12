<?php

use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AssessmentController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\GlossaryTermController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\LearningPathController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProtectionCategoryController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| The public read API for the InsuraLab frontend. All public content routes
| are unauthenticated, versioned under /api/v1, and rate-limited by the "api"
| throttle (see RouteServiceProvider). They only ever return content intended
| to be public — drafts, archived, and future-dated content are unreachable.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('knowledge', [\App\Http\Controllers\Api\V1\KnowledgeController::class, 'index'])->name('knowledge.index');
    Route::post('knowledge/assist', [\App\Http\Controllers\Api\V1\KnowledgeController::class, 'assist'])->middleware('throttle:12,1')->name('knowledge.assist');
    // Operational data stays unavailable until membership and per-record policies exist.
    Route::middleware('auth:sanctum')->get('agent/workspace', fn () => abort(403, 'Workspace agen belum diaktifkan.'))->name('agent.workspace');
    Route::get('health', HealthController::class)->name('health');

    Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

    Route::get('learning-paths', [LearningPathController::class, 'index'])->name('learning-paths.index');
    Route::get('learning-paths/{slug}', [LearningPathController::class, 'show'])->name('learning-paths.show');

    Route::get('protection-categories', [ProtectionCategoryController::class, 'index'])->name('protection-categories.index');

    Route::get('glossary', [GlossaryTermController::class, 'index'])->name('glossary.index');
    Route::get('glossary/{slug}', [GlossaryTermController::class, 'show'])->name('glossary.show');

    Route::get('providers', [ProviderController::class, 'index'])->name('providers.index');
    Route::get('providers/{slug}', [ProviderController::class, 'show'])->name('providers.show');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');

    Route::get('quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::post('quizzes/{slug}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('quizzes/{slug}', [QuizController::class, 'show'])->name('quizzes.show');

    Route::get('assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::post('assessments/{slug}/evaluate', [AssessmentController::class, 'evaluate'])->name('assessments.evaluate');
    Route::get('assessments/{slug}', [AssessmentController::class, 'show'])->name('assessments.show');
});

/*
| Admin/session-authenticated endpoints (Sanctum). Not part of the public API.
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
