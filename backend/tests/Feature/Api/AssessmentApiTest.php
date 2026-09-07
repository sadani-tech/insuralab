<?php

use App\Enums\ContentStatus;
use App\Models\Assessment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAssessment(ContentStatus $status = ContentStatus::Published): Assessment
{
    $assessment = Assessment::create([
        'title' => 'Protection Needs Assessment',
        'slug' => 'protection-needs-assessment',
        'description' => 'A short reflection.',
        'intro' => 'A learning tool, not advice.',
        'disclaimer' => 'InsuraLab does not provide personalized advice.',
        'status' => $status,
        'rules' => ['engine' => 'not_implemented'],
    ]);

    $question = $assessment->questions()->create([
        'prompt' => 'Does anyone depend on your income?',
        'help_text' => 'For example a partner or children.',
        'type' => 'single',
        'is_required' => true,
        'sort_order' => 0,
    ]);
    $question->options()->create(['label' => 'Yes, several people', 'weight' => 3, 'protection_category_key' => 'life', 'sort_order' => 0]);
    $question->options()->create(['label' => 'No, not right now', 'weight' => 0, 'protection_category_key' => null, 'sort_order' => 1]);

    return $assessment;
}

it('lists published assessment definitions', function () {
    makeAssessment();

    $this->getJson('/api/v1/assessments')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'protection-needs-assessment')
        ->assertJsonStructure(['data' => [['id', 'title', 'slug', 'questions_count']]]);
});

it('returns the assessment definition without scoring configuration', function () {
    makeAssessment();

    $response = $this->getJson('/api/v1/assessments/protection-needs-assessment')->assertOk();

    $response->assertJsonStructure([
        'data' => ['id', 'title', 'slug', 'intro', 'disclaimer', 'questions' => [['id', 'prompt', 'help_text', 'is_required', 'options' => [['id', 'label']]]]],
    ]);

    // Internal scoring config must not leak to the browser.
    expect($response->getContent())
        ->not->toContain('weight')
        ->not->toContain('protection_category_key')
        ->not->toContain('rules');
});

it('404s for an unknown or draft assessment', function () {
    makeAssessment(ContentStatus::Draft);

    $this->getJson('/api/v1/assessments/protection-needs-assessment')->assertNotFound();
    $this->getJson('/api/v1/assessments/none')->assertNotFound();
});
