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
        Schema::create('questions_skill_connects', function (Blueprint $table) {
            $table->id();
            $table->string('question');                // The main question text
            $table->text('hint_answer')->nullable();   // A hint or example answer (optional)
            $table->integer('order')->default(0);      // Order of display
            $table->boolean('is_active')->default(true); // Can deactivate without deleting
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions_skill_connects');
    }
};
