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
        Schema::create('add_flag_on_contents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('content_id');   // Content being flagged
            $table->unsignedBigInteger('flag_id');      // Type of flag (e.g. "misinformation")
            $table->unsignedBigInteger('flagged_by');   // User who flagged the content

            $table->boolean('is_reviewed')->default(false);
            $table->text('comment')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();

            // Prevent duplicate flagging of same content by same user
            $table->unique(['content_id', 'flagged_by'], 'unique_user_flag_per_content');

            // Optional foreign keys for data integrity
            // $table->foreign('content_id')->references('id')->on('contents')->onDelete('cascade');
            // $table->foreign('flag_id')->references('id')->on('content_flags')->onDelete('cascade');
            // $table->foreign('flagged_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_flag_on_contents');
    }
};
