<?php

use App\Enums\ProviderStatus;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a provider with valid attributes', function () {
    $provider = Provider::create([
        'name' => 'Example Assurance',
        'slug' => 'example-assurance',
        'description' => 'A test provider.',
        'country_of_origin' => 'Indonesia',
        'status' => ProviderStatus::Active,
        'last_reviewed_at' => now(),
    ]);

    expect($provider->exists)->toBeTrue()
        ->and($provider->status)->toBe(ProviderStatus::Active)
        ->and($provider->last_reviewed_at)->not->toBeNull();

    $this->assertDatabaseHas('providers', ['slug' => 'example-assurance']);
});

it('auto-generates a slug from the name when not provided', function () {
    $provider = Provider::create(['name' => 'Nusantara Life']);

    expect($provider->slug)->toBe('nusantara-life');
});

it('has many products', function () {
    $provider = Provider::factory()->create();
    Product::factory()->count(3)->create(['provider_id' => $provider->id]);

    expect($provider->products)->toHaveCount(3);
});

it('does not require a hardcoded Allianz table — Allianz is only data', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('allianz_products'))->toBeFalse()
        ->and(\Illuminate\Support\Facades\Schema::hasTable('allianz_articles'))->toBeFalse()
        ->and(\Illuminate\Support\Facades\Schema::hasTable('allianz_categories'))->toBeFalse();
});
