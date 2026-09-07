<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('product_category_id')->constrained('product_categories')->restrictOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable(); // Markdown

            $table->string('insurance_scheme')->default('conventional'); // conventional | sharia
            $table->string('product_structure')->default('other');       // traditional | paydi | other

            // Trust model (ADR-0004).
            $table->string('reference_status')->default('needs_review'); // current | historical | needs_review
            $table->string('reference_period')->nullable();
            $table->date('last_reviewed_at')->nullable();

            $table->string('status')->default('draft'); // draft | published | archived

            // Controlled, optional, extensible attributes only. Anything filterable
            // gets a real column (ADR-0002 / ARCHITECTURE §4.3). Not EAV.
            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            $table->index(['status', 'reference_status']);
            $table->index('provider_id');
            $table->index('insurance_scheme');
            $table->index('product_structure');
        });

        // A product responds to one or more protection concepts.
        Schema::create('product_protection_category', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('protection_category_id')->constrained('protection_categories')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->primary(['product_id', 'protection_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_protection_category');
        Schema::dropIfExists('products');
    }
};
