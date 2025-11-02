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
        Schema::create('life_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('event_type');
            $table->unsignedBigInteger('user_id');
            $table->boolean('isEducation')->default(false);
            $table->boolean('isOffice')->default(false);
            $table->boolean('isGeneral')->default(false);
            $table->unsignedBigInteger('icon_id')->nullable();
            $table->string('status')->default('active');
            $table->boolean('isPublic')->default(true);
            $table->timestamps();
    
            // Define foreign key constraint on user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('life_events');
    }
};
