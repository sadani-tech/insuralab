<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            // Nullable category — an article may be uncategorised while drafting.
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // NULLABLE ON PURPOSE (ADR-0002):
            //   provider_id IS NULL  -> general insurance knowledge
            //   provider_id = <id>   -> provider-specific educational content
            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body'); // Markdown

            $table->string('level')->default('beginner');   // beginner | intermediate | advanced
            $table->string('status')->default('draft');      // draft | published | archived
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('estimated_reading_minutes')->default(1);

            // Review / trust metadata (ADR-0004). Required by editorial rules for
            // provider-specific articles before they may be published; enforced in
            // the model + Filament form rather than at the DB level.
            $table->timestamp('published_at')->nullable();
            $table->date('last_reviewed_at')->nullable();
            $table->string('reference_period')->nullable();
            $table->string('reference_status')->nullable(); // current | historical | needs_review

            $table->timestamps();

            $table->index(['status', 'level']);
            $table->index('provider_id');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
