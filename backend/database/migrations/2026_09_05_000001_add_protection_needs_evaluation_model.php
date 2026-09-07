<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('methodology_version')->default('1.0')->after('status');
        });

        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->string('key')->nullable()->after('assessment_id');
            $table->string('depends_on_question_key')->nullable()->after('sort_order');
            $table->jsonb('applicable_option_keys')->nullable()->after('depends_on_question_key');
            $table->unique(['assessment_id', 'key']);
        });

        Schema::table('assessment_options', function (Blueprint $table) {
            $table->string('key')->nullable()->after('assessment_question_id');
            $table->unique(['assessment_question_id', 'key']);
        });

        Schema::create('assessment_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['assessment_id', 'key']);
        });

        Schema::create('assessment_option_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_option_id')->constrained('assessment_options')->cascadeOnDelete();
            $table->foreignId('assessment_dimension_id')->constrained('assessment_dimensions')->cascadeOnDelete();
            $table->unsignedSmallInteger('weight');
            $table->string('reason_code')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unique(['assessment_option_id', 'assessment_dimension_id']);
        });

        Schema::create('assessment_dimension_articles', function (Blueprint $table) {
            $table->foreignId('assessment_dimension_id')->constrained('assessment_dimensions')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['assessment_dimension_id', 'article_id']);
        });

        Schema::create('assessment_option_articles', function (Blueprint $table) {
            $table->foreignId('assessment_option_id')->constrained('assessment_options')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['assessment_option_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_option_articles');
        Schema::dropIfExists('assessment_dimension_articles');
        Schema::dropIfExists('assessment_option_impacts');
        Schema::dropIfExists('assessment_dimensions');

        Schema::table('assessment_options', function (Blueprint $table) {
            $table->dropUnique(['assessment_question_id', 'key']);
            $table->dropColumn('key');
        });

        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropUnique(['assessment_id', 'key']);
            $table->dropColumn(['key', 'depends_on_question_key', 'applicable_option_keys']);
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('methodology_version');
        });
    }
};
