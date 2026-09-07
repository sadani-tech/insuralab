<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reusable source records + a polymorphic pivot so one source (e.g. the
     * Allianz Basic Training PDF) can be attached to many entities without schema
     * changes (ADR-0004).
     */
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('publisher')->nullable();
            $table->string('url')->nullable();
            $table->date('published_at')->nullable();
            $table->date('accessed_at')->nullable();
            $table->string('reference_period')->nullable();
            $table->text('notes')->nullable(); // record source inconsistencies here, visibly
            $table->timestamps();
        });

        Schema::create('sourceables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->morphs('sourceable'); // sourceable_type + sourceable_id (+ index)
            $table->text('note')->nullable(); // per-attachment note (e.g. "page 14")
            $table->timestamps();

            $table->unique(['source_id', 'sourceable_type', 'sourceable_id'], 'sourceables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sourceables');
        Schema::dropIfExists('sources');
    }
};
