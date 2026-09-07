<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProtectionCategory;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('belongs to a provider and a product category', function () {
    $provider = Provider::factory()->create();
    $category = ProductCategory::factory()->create();

    $product = Product::factory()->create([
        'provider_id' => $provider->id,
        'product_category_id' => $category->id,
    ]);

    expect($product->provider->is($provider))->toBeTrue()
        ->and($product->productCategory->is($category))->toBeTrue();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'provider_id' => $provider->id,
        'product_category_id' => $category->id,
    ]);
});

it('is deleted when its provider is deleted (cascade)', function () {
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $provider->delete();

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

it('relates to one or more protection concepts', function () {
    $product = Product::factory()->create();
    $life = ProtectionCategory::factory()->create(['key' => 'life-'.uniqid()]);
    $health = ProtectionCategory::factory()->create(['key' => 'health-'.uniqid()]);

    $product->protectionCategories()->attach([
        $life->id => ['is_primary' => true],
        $health->id => ['is_primary' => false],
    ]);

    expect($product->protectionCategories)->toHaveCount(2)
        ->and($product->protectionCategories->firstWhere('id', $life->id)->pivot->is_primary)->toBeTruthy();
});
