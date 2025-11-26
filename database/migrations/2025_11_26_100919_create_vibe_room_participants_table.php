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
        Schema::create('vibe_room_participants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vibe_room_id');   // FK -> vibe_rooms
            $table->unsignedBigInteger('user_id');        // FK -> users table

            $table->enum('role', ['host', 'participant'])
                  ->default('participant');

            $table->boolean('is_anonymous')
                  ->default(true)
                  ->comment('true = hidden name, false = visible');

            $table->integer('guess_progress')
                  ->default(0)
                  ->comment('percent of identity revealed (0–100)');

            $table->boolean('is_kicked')
                  ->default(false);

            $table->boolean('is_banned')
                  ->default(false)
                  ->comment('prevent rejoining');

            $table->timestamp('last_active_at')->nullable();

            $table->timestamps();

            // Relations
            $table->foreign('vibe_room_id')
                  ->references('id')
                  ->on('vibe_rooms')
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
        Schema::dropIfExists('vibe_room_participants');
    }
};
