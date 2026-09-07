<?php

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\Assessment;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\AssessmentSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GlossaryTermSeeder;
use Database\Seeders\LearningPathSeeder;
use Database\Seeders\ProviderSeeder;
use Database\Seeders\QuizSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AssessmentSeeder::class);
});

function assessmentAnswers(array $overrides = []): array
{
    $answers = array_merge([
        'dependents' => 'none',
        'income_role' => 'not_primary',
        'emergency_buffer' => 'over_twelve',
        'health_coverage' => 'employer_or_bpjs',
        'financial_obligations' => 'none',
        'serious_illness_impact' => 'small',
        'alternative_income' => 'adequate',
        'accident_interest' => 'not_much',
        'insurance_understanding' => 'understood',
        'primary_concern' => 'unknown',
    ], $overrides);

    if ($answers['dependents'] !== 'none') {
        $answers['dependent_health_coverage'] = $overrides['dependent_health_coverage'] ?? 'yes';
    } else {
        unset($answers['dependent_health_coverage']);
    }

    return collect($answers)->map(fn ($option, $question) => compact('question', 'option'))->values()->all();
}

function evaluateAssessment($test, array $overrides = [])
{
    return $test->postJson('/api/v1/assessments/protection-needs/evaluate', [
        'methodology_version' => '1.0',
        'answers' => assessmentAnswers($overrides),
    ]);
}

it('returns the public protection needs definition with semantic keys and methodology version', function () {
    $this->getJson('/api/v1/assessments/protection-needs')
        ->assertOk()
        ->assertJsonPath('data.slug', 'protection-needs')
        ->assertJsonPath('data.methodology_version', '1.0')
        ->assertJsonCount(11, 'data.questions')
        ->assertJsonPath('data.questions.0.key', 'dependents')
        ->assertJsonPath('data.questions.4.condition.question', 'dependents');
});

it('does not expose an inactive protection needs assessment', function () {
    Assessment::where('slug', 'protection-needs')->update(['status' => ContentStatus::Draft->value]);
    $this->getJson('/api/v1/assessments/protection-needs')->assertNotFound();
    evaluateAssessment($this)->assertNotFound();
});

it('enforces required applicable questions', function () {
    $answers = assessmentAnswers();
    array_pop($answers);
    $this->postJson('/api/v1/assessments/protection-needs/evaluate', [
        'methodology_version' => '1.0', 'answers' => $answers,
    ])->assertUnprocessable()->assertJsonValidationErrors('answers');
});

it('rejects unknown questions invalid options and options from another question', function () {
    $unknownQuestion = assessmentAnswers();
    $unknownQuestion[0]['question'] = 'not_a_question';
    $this->postJson('/api/v1/assessments/protection-needs/evaluate', ['methodology_version' => '1.0', 'answers' => $unknownQuestion])
        ->assertUnprocessable()->assertJsonValidationErrors('answers.0.question');

    $invalidOption = assessmentAnswers();
    $invalidOption[0]['option'] = 'not_an_option';
    $this->postJson('/api/v1/assessments/protection-needs/evaluate', ['methodology_version' => '1.0', 'answers' => $invalidOption])
        ->assertUnprocessable()->assertJsonValidationErrors('answers.0.option');

    $wrongQuestionOption = assessmentAnswers();
    $wrongQuestionOption[0]['option'] = 'primary';
    $this->postJson('/api/v1/assessments/protection-needs/evaluate', ['methodology_version' => '1.0', 'answers' => $wrongQuestionOption])
        ->assertUnprocessable()->assertJsonValidationErrors('answers.0.option');
});

it('rejects duplicate conflicting answers', function () {
    $answers = assessmentAnswers();
    $answers[] = ['question' => 'dependents', 'option' => 'one'];
    $this->postJson('/api/v1/assessments/protection-needs/evaluate', ['methodology_version' => '1.0', 'answers' => $answers])
        ->assertUnprocessable()->assertJsonValidationErrors('answers');
});

it('handles conditional questions and rejects a skipped question when it is not applicable', function () {
    evaluateAssessment($this)->assertOk();

    $answers = assessmentAnswers();
    $answers[] = ['question' => 'dependent_health_coverage', 'option' => 'none'];
    $this->postJson('/api/v1/assessments/protection-needs/evaluate', ['methodology_version' => '1.0', 'answers' => $answers])
        ->assertUnprocessable()->assertJsonValidationErrors('answers');

    evaluateAssessment($this, ['dependents' => 'one', 'dependent_health_coverage' => 'none'])->assertOk();
});

it('rejects a stale methodology version', function () {
    $this->postJson('/api/v1/assessments/protection-needs/evaluate', [
        'methodology_version' => '0.9', 'answers' => assessmentAnswers(),
    ])->assertUnprocessable()->assertJsonValidationErrors('methodology_version');
});

it('is deterministic and returns normalized relevance tiers without public scores', function () {
    $answers = ['dependents' => 'two_to_three', 'income_role' => 'primary', 'financial_obligations' => 'significant'];
    $first = evaluateAssessment($this, $answers)->assertOk()->json('data');
    $second = evaluateAssessment($this, $answers)->assertOk()->json('data');

    expect($second)->toBe($first)
        ->and(json_encode($first))->not->toContain('max_score')->not->toContain('normalized_score');
});

it('maps normalized results across foundational worth exploring and high thresholds', function () {
    $worthExploring = collect(evaluateAssessment($this, [
        'dependents' => 'two_to_three', 'income_role' => 'one_of_sources',
    ])->assertOk()->json('data.dimensions'))->firstWhere('key', 'life');
    $high = collect(evaluateAssessment($this, [
        'dependents' => 'two_to_three', 'income_role' => 'only_source',
        'emergency_buffer' => 'under_one_month', 'financial_obligations' => 'significant',
        'primary_concern' => 'family_after_death',
    ])->assertOk()->json('data.dimensions'))->firstWhere('key', 'life');

    expect($worthExploring['relevance'])->toBe('worth_exploring')
        ->and($high['relevance'])->toBe('highly_relevant');
});

it('rejects nonsensical methodology thresholds and conditional references at the model boundary', function () {
    $assessment = Assessment::where('slug', 'protection-needs')->firstOrFail();

    expect(function () use ($assessment) {
        $assessment->rules = array_merge($assessment->rules, [
            'thresholds' => ['worth_exploring' => 0.80, 'highly_relevant' => 0.50],
        ]);
        $assessment->save();
    })->toThrow(ValidationException::class);

    expect(fn () => $assessment->questions()->create([
        'key' => 'invalid_condition',
        'prompt' => 'Invalid condition',
        'type' => 'single',
        'is_required' => true,
        'sort_order' => 99,
        'depends_on_question_key' => 'missing_question',
        'applicable_option_keys' => ['missing_option'],
    ]))->toThrow(ValidationException::class);
});

it('persona A single user with a strong buffer does not get artificially high life relevance', function () {
    $dimensions = collect(evaluateAssessment($this, [
        'dependents' => 'none', 'income_role' => 'not_primary', 'emergency_buffer' => 'over_twelve',
        'health_coverage' => 'private', 'alternative_income' => 'adequate',
    ])->assertOk()->json('data.dimensions'))->keyBy('key');

    expect($dimensions['life']['relevance'])->toBe('foundational');
});

it('persona B primary income earner with dependents gets high life learning relevance', function () {
    $dimensions = collect(evaluateAssessment($this, [
        'dependents' => 'two_to_three', 'dependent_health_coverage' => 'partial',
        'income_role' => 'only_source', 'emergency_buffer' => 'under_one_month',
        'financial_obligations' => 'significant', 'primary_concern' => 'family_after_death',
    ])->assertOk()->json('data.dimensions'))->keyBy('key');

    expect($dimensions['life']['relevance'])->toBe('highly_relevant')
        ->and($dimensions['life']['reasons'])->toContain('Kamu menunjukkan bahwa ada beberapa orang yang bergantung pada penghasilanmu.');
});

it('persona C existing employer or BPJS coverage is respected and prompts policy learning', function () {
    $this->seed([ProviderSeeder::class, GlossaryTermSeeder::class, CategorySeeder::class, ArticleSeeder::class, AssessmentSeeder::class]);
    $data = evaluateAssessment($this, [
        'health_coverage' => 'employer_or_bpjs', 'insurance_understanding' => 'not_understood',
    ])->assertOk()->json('data');
    $health = collect($data['dimensions'])->firstWhere('key', 'health');

    expect($health['reasons'][0])->toContain('BPJS atau perusahaan')
        ->and(json_encode($data))->not->toContain('belum memiliki perlindungan kesehatan')
        ->and($data['additional_learning']['topics'])->not->toBeEmpty();
});

it('persona D without dependents but with severe work interruption impact prioritizes disability over life', function () {
    $dimensions = collect(evaluateAssessment($this, [
        'dependents' => 'none', 'income_role' => 'primary', 'emergency_buffer' => 'under_one_month',
        'serious_illness_impact' => 'very_large', 'alternative_income' => 'none',
        'primary_concern' => 'income_loss',
    ])->assertOk()->json('data.dimensions'));

    expect($dimensions->firstWhere('key', 'disability')['relevance'])->toBe('highly_relevant')
        ->and($dimensions->firstWhere('key', 'life')['relevance'])->not->toBe('highly_relevant');
});

it('persona E unknown answers produce foundational learning rather than maximum relevance', function () {
    $dimensions = collect(evaluateAssessment($this, [
        'emergency_buffer' => 'unknown', 'health_coverage' => 'unknown',
        'serious_illness_impact' => 'unknown', 'alternative_income' => 'unknown',
        'accident_interest' => 'unknown', 'primary_concern' => 'unknown',
    ])->assertOk()->json('data.dimensions'));

    expect($dimensions->where('relevance', 'highly_relevant'))->toHaveCount(0);
});

it('returns only general education and gracefully omits missing optional articles', function () {
    $this->seed([ProviderSeeder::class, GlossaryTermSeeder::class, CategorySeeder::class, ArticleSeeder::class, AssessmentSeeder::class]);
    Article::where('slug', 'asuransi-jiwa')->delete();
    $response = evaluateAssessment($this, ['dependents' => 'two_to_three'])->assertOk();
    $content = $response->getContent();

    expect($content)->not->toContain('allianz')->not->toContain('product_recommendation');
    collect($response->json('data.dimensions'))->flatMap(fn ($dimension) => $dimension['learning_topics'])
        ->each(fn ($topic) => expect($topic['slug'])->not->toBe('asuransi-jiwa'));
});

it('links assessment dimensions to a general learning path when one is published', function () {
    $this->seed([
        ProviderSeeder::class,
        GlossaryTermSeeder::class,
        CategorySeeder::class,
        ArticleSeeder::class,
        QuizSeeder::class,
        LearningPathSeeder::class,
        AssessmentSeeder::class,
    ]);

    $dimensions = evaluateAssessment($this, ['dependents' => 'two_to_three'])
        ->assertOk()
        ->json('data.dimensions');

    expect(collect($dimensions)->firstWhere('key', 'life')['learning_path'])
        ->toMatchArray([
            'slug' => 'memahami-proteksi-pribadi',
            'url' => '/learn/path/memahami-proteksi-pribadi',
        ]);
});

it('does not persist anonymous answer sets', function () {
    $assessmentCount = Assessment::count();
    evaluateAssessment($this, ['dependents' => 'one'])->assertOk();

    expect(Assessment::count())->toBe($assessmentCount)
        ->and(Schema::hasTable('assessment_responses'))->toBeFalse()
        ->and(Schema::hasTable('assessment_answers'))->toBeFalse();
});
