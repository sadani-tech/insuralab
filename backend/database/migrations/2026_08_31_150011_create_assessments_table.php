<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Assessment definitions only. NO evaluation engine in Phase 2 (ROADMAP §7).
     * Options carry `weight` + `protection_category_key` so future evaluation
     * rules can be configuration, not code — and can only ever map to LEARNING
     * TOPICS / protection categories, never to "buy product X".
     */
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('intro')->nullable();
            $table->text('disclaimer')->nullable();
            $table->string('status')->default('draft'); // draft | published | archived
            $table->jsonb('rules')->nullable();          // reserved for the future configurable engine
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->text('prompt');
            $table->text('help_text')->nullable();
            $table->string('type')->default('single'); // single | multiple | scale
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('assessment_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_question_id')->constrained('assessment_questions')->cascadeOnDelete();
            $table->text('label');
            $table->integer('weight')->default(0);
            // Which protection topic this answer nudges the learner toward.
            $table->string('protection_category_key')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_options');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }
};
