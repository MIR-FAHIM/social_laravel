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
        Schema::create('answer_skill_connects', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('question_id')->index(); // Linked to questions_skill_connects.id
            $table->unsignedBigInteger('user_id')->index();     // Linked to users.id

            // Main data fields
            $table->text('answer')->nullable();                 // The actual answer or description
            $table->boolean('is_bullet')->default(false);       // Whether to display answer as bullet points
            $table->string('type')->default('text');            // text, image, video, or link (for flexible content)

            // Optional fields
            $table->boolean('is_active')->default(true);        // Enable/disable specific answer visibility
            $table->timestamps();

            // Foreign key relationships
            $table->foreign('question_id')
                ->references('id')
                ->on('questions_skill_connects')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_skill_connects');
    }
};
