<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glossary_terms', function (Blueprint $table) {
            $table->id();

            // Nullable — most terms are general; a term may be provider-specific.
            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();

            $table->string('term');
            $table->string('slug')->unique();
            $table->text('short_definition');           // tooltip length
            $table->longText('definition');             // full explanation (Markdown)
            $table->string('category')->default('general'); // general | sharia | product | process | compliance | agency
            $table->jsonb('metadata')->nullable();
            $table->date('last_reviewed_at')->nullable();
            $table->string('reference_status')->nullable(); // current | historical | needs_review
            $table->timestamps();

            $table->index('category');
        });

        // Self-referential many-to-many: related terms.
        Schema::create('glossary_term_related', function (Blueprint $table) {
            $table->foreignId('glossary_term_id')->constrained('glossary_terms')->cascadeOnDelete();
            $table->foreignId('related_term_id')->constrained('glossary_terms')->cascadeOnDelete();
            $table->primary(['glossary_term_id', 'related_term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glossary_term_related');
        Schema::dropIfExists('glossary_terms');
    }
};
