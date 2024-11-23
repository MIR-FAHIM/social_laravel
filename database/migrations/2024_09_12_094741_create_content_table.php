<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTable extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('text_content')->nullable();
            $table->text('text_title')->nullable();
            $table->text('text_url')->nullable();
            $table->boolean('isGeneral')->default(false);
            $table->boolean('isDiscussion')->default(false);
            $table->boolean('isNews')->default(false);
            $table->boolean('isEducation')->default(false);
            $table->string('single_image')->nullable();
            $table->boolean('isFired')->default(false);
            $table->boolean('isBurnt')->default(false);
            $table->integer('score')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->boolean('is_authenticated')->default(false);
            $table->boolean(column: 'is_author_writting')->default(false);
            $table->boolean('is_debate')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posted_contents');
    }
}
