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
        Schema::create('mood_masters', function (Blueprint $table) {
            $table->id();
            
            $table->string('mood_name', 100);
            $table->string('mood_icon')->nullable();        // URL or filename
            $table->string('mood_color', 20)->nullable();   // HEX color code
            $table->text('description')->nullable();        // Optional text
            
            $table->boolean('is_active')->default(1);       // 1 = active, 0 = hidden
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mood_masters');
    }
};
