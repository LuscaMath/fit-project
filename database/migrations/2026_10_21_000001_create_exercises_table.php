<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('modality', 20);
            $table->string('measurement_type', 30);
            $table->string('muscle_group');
            $table->string('equipment')->nullable();
            $table->text('instructions');
            $table->string('video_url', 2048)->nullable();
            $table->timestamps();

            $table->index('modality');
            $table->index('muscle_group');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
