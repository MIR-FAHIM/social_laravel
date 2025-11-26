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
        Schema::create('vibe_room_messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vibe_room_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('participant_id')->nullable();

            $table->text('message_content')->nullable();

            // anonymous mode
            $table->boolean('is_anonymous')->default(true);

            // smart reactions (stored as JSON)
            $table->json('reactions')->nullable();

            // guessing system per message
            $table->integer('guess_progress')->default(0);

            // moderation flags
            $table->boolean('is_flagged')->default(false);
            $table->boolean('is_hidden')->default(false);

            $table->timestamps();

            // Foreign keys
            $table->foreign('vibe_room_id')->references('id')->on('vibe_rooms')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('vibe_room_participants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vibe_room_messages');
    }
};
