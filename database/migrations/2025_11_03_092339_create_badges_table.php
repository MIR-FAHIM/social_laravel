<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();

            // Core identity
            $table->string('name');                  // e.g., "Firekeeper"
            $table->string('slug')->unique();        // e.g., "firekeeper" (for URLs / lookups)
            $table->string('icon')->nullable();      // path or URL to icon asset

            // Semantics
            $table->string('role')->nullable();      // short role label, e.g., "Guardian of accountability"
            $table->text('power')->nullable();       // describe what it can do
            $table->text('limitation')->nullable();  // describe constraints/quotas/cooldowns

            // Ecosystem knobs
            $table->boolean('is_active')->default(true)->index();  // toggle availability
            $table->unsignedInteger('count')->default(0);          // global usage/grants counter

            // Rich metadata (flexible)
            $table->json('rules')->nullable();       // JSON rules: thresholds, quotas, prerequisites
            $table->json('tips')->nullable();        // JSON array of tips/UX copy

            // Housekeeping
            $table->timestamps();
            $table->softDeletes();                   // optional but handy for safe removal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
