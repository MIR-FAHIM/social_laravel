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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receiver_id'); // ID of the user receiving the notification
            $table->unsignedBigInteger('sender_id'); // ID of the user receiving the notification
            $table->unsignedBigInteger('content_id')->nullable(); // Content ID, nullable
            $table->unsignedBigInteger('friend_req_id')->nullable(); // Content ID, nullable
            $table->string('type'); // Notification title
            $table->string('title'); // Notification title
            $table->text('body'); // Notification body
            $table->boolean('is_read')->default(false); // Status if the notification is read
            $table->timestamps();

            // Foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sent_to')->references('id')->on('users')->onDelete('cascade');
            // Foreign key constraint for content_id, if there is a content table
            $table->foreign('content_id')->references('id')->on('contents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
