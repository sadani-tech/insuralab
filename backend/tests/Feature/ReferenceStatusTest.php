<?php

use App\Enums\ReferenceStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('defines exactly the three trust states', function () {
    expect(array_map(fn ($c) => $c->value, ReferenceStatus::cases()))
        ->toEqualCanonicalizing(['current', 'historical', 'needs_review']);
});

it('casts a product reference_status to the enum', function () {
    $product = Product::factory()->create(['reference_status' => ReferenceStatus::Historical]);

    expect($product->fresh()->reference_status)->toBe(ReferenceStatus::Historical);
});

it('accepts each valid reference status value', function (string $value) {
    $product = Product::factory()->create(['reference_status' => $value]);

    expect($product->fresh()->reference_status)->toBeInstanceOf(ReferenceStatus::class)
        ->and($product->fresh()->reference_status->value)->toBe($value);
})->with(['current', 'historical', 'needs_review']);

it('exposes label and colour for admin display', function () {
    expect(ReferenceStatus::NeedsReview->getLabel())->toBe('Needs review')
        ->and(ReferenceStatus::NeedsReview->getColor())->toBe('danger')
        ->and(ReferenceStatus::Current->getColor())->toBe('success')
        ->and(ReferenceStatus::Historical->getColor())->toBe('warning');
});
