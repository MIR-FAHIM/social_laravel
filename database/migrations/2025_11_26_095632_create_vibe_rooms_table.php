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
        Schema::create('vibe_rooms', function (Blueprint $table) {
            $table->id();

            // Host of the room
            $table->unsignedBigInteger('host_user_id');

            // Mood reference
            $table->unsignedBigInteger('mood_id');

            // Room metadata
            $table->string('room_title', 255);
            $table->text('vibe_details')->nullable();

            // When the vibe room expires
            $table->dateTime('expire_time');

            // Room settings
            $table->boolean('allow_guessing')->default(true);
            $table->boolean('allow_reveal')->default(true);

            // Room status
            $table->boolean('is_active')->default(true);

            // UI color chosen by host
            $table->string('color', 30)->nullable();

            $table->timestamps();

            // Indexing for better performance
            $table->index('host_user_id');
            $table->index('mood_id');
            $table->index('expire_time');

            // Optional foreign keys (uncomment if users & moods table exist)
            // $table->foreign('host_user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('mood_id')->references('id')->on('mood_masters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vibe_rooms');
    }
};
