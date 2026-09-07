<?php

use App\Models\GlossaryTerm;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists glossary terms alphabetically with tooltip-length fields only', function () {
    GlossaryTerm::factory()->create(['term' => 'Zebra clause', 'slug' => 'zebra-clause']);
    GlossaryTerm::factory()->create(['term' => 'Actuary', 'slug' => 'actuary']);

    $response = $this->getJson('/api/v1/glossary')->assertOk()
        ->assertJsonStructure(['data' => [['id', 'term', 'slug', 'short_definition', 'category', 'letter']], 'meta']);

    expect($response->json('data.0.term'))->toBe('Actuary');
    // list payload must not carry the full definition
    expect($response->json('data.0'))->not->toHaveKey('definition');
});

it('filters by first letter (case-insensitive)', function () {
    GlossaryTerm::factory()->create(['term' => 'Annuity', 'slug' => 'annuity']);
    GlossaryTerm::factory()->create(['term' => 'Beneficiary', 'slug' => 'beneficiary-x']);

    $data = $this->getJson('/api/v1/glossary?letter=a')->assertOk()->json('data');

    expect(collect($data)->pluck('term'))->toEqual(collect(['Annuity']));
});

it('filters by category and by free-text query', function () {
    GlossaryTerm::factory()->create(['term' => 'Tabarru', 'slug' => 'tabarru', 'category' => 'sharia', 'definition' => 'mutual fund pool']);
    GlossaryTerm::factory()->create(['term' => 'Deductible', 'slug' => 'deductible', 'category' => 'general', 'definition' => 'own risk portion']);

    expect(collect($this->getJson('/api/v1/glossary?category=sharia')->json('data'))->pluck('slug'))
        ->toEqual(collect(['tabarru']));

    expect(collect($this->getJson('/api/v1/glossary?q=own risk')->json('data'))->pluck('slug'))
        ->toEqual(collect(['deductible']));
});

it('retrieves a term by slug with the full definition and sources', function () {
    $term = GlossaryTerm::factory()->create(['term' => 'Indemnity', 'slug' => 'indemnity']);
    $source = Source::factory()->create(['title' => 'Glossary Source']);
    $term->sources()->attach($source->id, ['note' => 'ref']);

    $this->getJson('/api/v1/glossary/indemnity')
        ->assertOk()
        ->assertJsonPath('data.slug', 'indemnity')
        ->assertJsonStructure(['data' => ['definition', 'trust', 'related_terms', 'sources']])
        ->assertJsonPath('data.sources.0.title', 'Glossary Source');
});

it('returns 404 for an unknown term', function () {
    $this->getJson('/api/v1/glossary/missing-term')->assertNotFound();
});
