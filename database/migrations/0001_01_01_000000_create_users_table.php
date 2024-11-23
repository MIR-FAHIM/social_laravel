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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            
            // New fields
            $table->boolean('is_guard')->default(false);
            $table->boolean('is_fireman')->default(false);
            $table->boolean('is_fire_fighter')->default(false);
            $table->boolean('is_peace_keeper')->default(false);
            $table->boolean('is_guardian_angel')->default(false);
            $table->boolean('is_weesdom_keeper')->default(false);
            $table->boolean('is_shadow_hunter')->default(false);
            $table->boolean('is_whistleblower')->default(false);
            $table->boolean('isAuthor')->default(false);
            $table->string('fcm_token')->nullable();
            $table->boolean('is_community_builder')->default(false);
            $table->string('mobile')->nullable();

            $table->timestamps();
        });

      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
