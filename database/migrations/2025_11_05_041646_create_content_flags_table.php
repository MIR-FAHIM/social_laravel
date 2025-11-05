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
        Schema::create('content_flags', function (Blueprint $table) {
            $table->id();

            $table->string('flag_name')->unique(); // name of the flag, e.g. “Spam”, “Violence”
            $table->text('note')->nullable();      // description or moderation note
            $table->integer('score')->default(0);  // numeric impact or weight
            $table->string('icon')->nullable();    // path or icon name
            $table->boolean('is_active')->default(true); // status toggle

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_flags');
    }
};
