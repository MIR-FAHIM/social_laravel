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
        Schema::create('badges_gains', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('badge_id')->index();

            // Badge progress & status
            $table->boolean('is_active')->default(true)->index(); // Whether the user currently holds the badge
            $table->unsignedDecimal('percentage', 5, 2)->default(0); // Progress toward earning (0–100)
            $table->unsignedInteger('count')->default(0); // How many times earned / used
            $table->text('note')->nullable(); // Optional note or moderator comment

            $table->timestamps();

    

            // Unique index to prevent duplicates
            $table->unique(['user_id', 'badge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges_gains');
    }
};
