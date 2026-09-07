<?php

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeQuiz(ContentStatus $status = ContentStatus::Published): Quiz
{
    $quiz = Quiz::create([
        'category_id' => Category::factory()->create(['name' => 'Fundamentals', 'slug' => 'fundamentals'])->id,
        'title' => 'Fundamentals self-check', 'slug' => 'fundamentals-self-check',
        'description' => 'Low-stakes check.', 'level' => 'beginner',
        'estimated_duration_minutes' => 5, 'status' => $status,
    ]);

    $article = Article::factory()->published()->create(['title' => 'Apa Itu Risiko']);
    $first = $quiz->questions()->create([
        'key' => 'risk-impact', 'prompt' => 'What does insurance protect against?',
        'explanation' => 'It addresses the financial consequence.', 'type' => 'single', 'sort_order' => 0,
    ]);
    $first->options()->create(['key' => 'financial-impact', 'label' => 'The financial impact', 'is_correct' => true, 'explanation' => 'Tepat. Insurance responds to financial impact.', 'sort_order' => 0]);
    $first->options()->create(['key' => 'event', 'label' => 'The event itself', 'is_correct' => false, 'explanation' => 'Belum tepat. Insurance does not stop the event.', 'sort_order' => 1]);
    $first->relatedArticles()->attach($article->id, ['sort_order' => 0]);

    $second = $quiz->questions()->create([
        'key' => 'claim-authority', 'prompt' => 'Who decides a claim?',
        'explanation' => 'The insurer evaluates the policy.', 'type' => 'single', 'sort_order' => 1,
    ]);
    $second->options()->create(['key' => 'insurer', 'label' => 'The insurer', 'is_correct' => true, 'explanation' => 'Tepat. The insurer evaluates it.', 'sort_order' => 0]);
    $second->options()->create(['key' => 'agent', 'label' => 'The agent', 'is_correct' => false, 'explanation' => 'Belum tepat. An agent may assist but does not decide.', 'sort_order' => 1]);

    return $quiz;
}

it('lists published quizzes with useful educational metadata', function () {
    makeQuiz();
    Quiz::create(['title' => 'Hidden', 'slug' => 'hidden-quiz', 'level' => 'beginner', 'status' => ContentStatus::Draft]);

    $this->getJson('/api/v1/quizzes')->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'fundamentals-self-check')
        ->assertJsonPath('data.0.category.slug', 'fundamentals')
        ->assertJsonPath('data.0.estimated_duration_minutes', 5)
        ->assertJsonStructure(['data' => [['title', 'slug', 'level', 'questions_count', 'category', 'related_learning_paths']]]);
});

it('returns quiz questions without leaking correctness or explanations', function () {
    makeQuiz();
    $response = $this->getJson('/api/v1/quizzes/fundamentals-self-check')->assertOk()
        ->assertJsonStructure(['data' => ['title', 'slug', 'questions' => [['key', 'prompt', 'type', 'options' => [['key', 'label']]]]]]);

    expect($response->getContent())->not->toContain('is_correct')
        ->not->toContain('correct_option')
        ->not->toContain('"explanation"');
});

it('scores a valid complete submission and returns educational review data', function () {
    makeQuiz();

    $response = $this->postJson('/api/v1/quizzes/fundamentals-self-check/submit', [
        'answers' => [
            ['question' => 'risk-impact', 'option' => 'event'],
            ['question' => 'claim-authority', 'option' => 'insurer'],
        ],
    ])->assertOk();

    $response->assertJsonPath('data.score.correct', 1)
        ->assertJsonPath('data.score.total', 2)
        ->assertJsonPath('data.score.percentage', 50)
        ->assertJsonPath('data.results.0.correct', false)
        ->assertJsonPath('data.results.0.correct_option', 'financial-impact')
        ->assertJsonPath('data.results.1.correct', true)
        ->assertJsonPath('data.review_topics.0.title', 'Apa Itu Risiko')
        ->assertJsonStructure(['data' => ['results' => [['explanation', 'concept_explanation', 'related_lessons']]]]);
});

it('rejects an unknown option', function () {
    makeQuiz();
    $this->postJson('/api/v1/quizzes/fundamentals-self-check/submit', ['answers' => [
        ['question' => 'risk-impact', 'option' => 'unknown'],
        ['question' => 'claim-authority', 'option' => 'insurer'],
    ]])->assertUnprocessable()->assertJsonValidationErrors('answers.0.option');
});

it('rejects an option owned by a different question', function () {
    makeQuiz();
    $this->postJson('/api/v1/quizzes/fundamentals-self-check/submit', ['answers' => [
        ['question' => 'risk-impact', 'option' => 'insurer'],
        ['question' => 'claim-authority', 'option' => 'agent'],
    ]])->assertUnprocessable()->assertJsonValidationErrors('answers.0.option');
});

it('rejects duplicate answers', function () {
    makeQuiz();
    $this->postJson('/api/v1/quizzes/fundamentals-self-check/submit', ['answers' => [
        ['question' => 'risk-impact', 'option' => 'event'],
        ['question' => 'risk-impact', 'option' => 'financial-impact'],
    ]])->assertUnprocessable()->assertJsonValidationErrors('answers');
});

it('rejects a missing required answer', function () {
    makeQuiz();
    $this->postJson('/api/v1/quizzes/fundamentals-self-check/submit', ['answers' => [
        ['question' => 'risk-impact', 'option' => 'financial-impact'],
    ]])->assertUnprocessable()->assertJsonValidationErrors('answers');
});

it('404s for an unknown or draft quiz definition and submission', function () {
    makeQuiz(ContentStatus::Draft);
    $this->getJson('/api/v1/quizzes/fundamentals-self-check')->assertNotFound();
    $this->postJson('/api/v1/quizzes/fundamentals-self-check/submit', ['answers' => [
        ['question' => 'risk-impact', 'option' => 'financial-impact'],
    ]])->assertNotFound();
    $this->getJson('/api/v1/quizzes/nope')->assertNotFound();
});
