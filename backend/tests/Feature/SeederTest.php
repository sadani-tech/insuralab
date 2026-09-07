<?php

use App\Models\Article;
use App\Models\Assessment;
use App\Models\GlossaryTerm;
use App\Models\LearningPath;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Quiz;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('runs the full database seeder to completion', function () {
    expect(true)->toBeTrue(); // reaching here means seed() did not throw
});

it('seeds at least the required volume of content', function () {
    expect(Article::count())->toBeGreaterThanOrEqual(15)
        ->and(GlossaryTerm::count())->toBeGreaterThanOrEqual(40);
});

it('seeds both general and provider-specific articles', function () {
    expect(Article::whereNull('provider_id')->count())->toBeGreaterThan(0)
        ->and(Article::whereNotNull('provider_id')->count())->toBeGreaterThan(0);
});

it('seeds Allianz only as a provider row, never as its own table', function () {
    expect(Provider::where('slug', 'allianz')->exists())->toBeTrue()
        ->and(Schema::hasTable('allianz_products'))->toBeFalse();
});

it('seeds products that belong to the provider and carry review metadata', function () {
    $products = Product::all();

    expect($products->count())->toBeGreaterThan(0);
    $products->each(function (Product $p) {
        expect($p->provider_id)->not->toBeNull()
            ->and($p->product_category_id)->not->toBeNull()
            ->and($p->reference_status)->not->toBeNull();
    });
});

it('seeds at least one needs_review item capturing a source inconsistency', function () {
    expect(Product::where('reference_status', 'needs_review')->count())->toBeGreaterThan(0)
        ->and(Product::where('reference_status', 'historical')->count())->toBeGreaterThan(0)
        ->and(Source::where('notes', 'like', '%Option 3%')->exists())->toBeTrue();
});

it('attaches the training source to the provider and products', function () {
    $allianz = Provider::where('slug', 'allianz')->first();

    expect($allianz->sources()->count())->toBeGreaterThan(0)
        ->and(Product::has('sources')->count())->toBeGreaterThan(0);
});

it('seeds the Phase 8 quiz and learning journey catalog', function () {
    expect(Quiz::has('questions')->count())->toBeGreaterThanOrEqual(6)
        ->and(Assessment::has('questions')->count())->toBeGreaterThan(0)
        ->and(Assessment::has('dimensions')->count())->toBeGreaterThan(0)
        ->and(LearningPath::has('items')->count())->toBeGreaterThanOrEqual(5)
        ->and(Quiz::query()->withCount('questions')->get()->min('questions_count'))->toBeGreaterThanOrEqual(5);
});

it('gives every quiz option an explanation and each question a correct answer', function () {
    Quiz::with('questions.options')->get()->each(fn (Quiz $quiz) => $quiz->questions->each(function ($q) {
        expect($q->options->where('is_correct', true)->count())->toBe(1);
        $q->options->each(fn ($o) => expect($o->explanation)->not->toBeNull());
    }));
});

it('never maps an assessment option to a product recommendation', function () {
    $assessment = Assessment::with('questions.options')->first();

    $assessment->questions->each(function ($q) {
        $q->options->each(function ($o) {
            // Options may only point at a protection topic key, never a product id.
            expect($o->getAttributes())->not->toHaveKey('product_id');
        });
    });

    expect($assessment->rules['forbidden_output'] ?? [])->toContain('product_recommendation');
});

it('creates an admin user from configuration', function () {
    expect(User::where('is_admin', true)->count())->toBeGreaterThan(0);
});
