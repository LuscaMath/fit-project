<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->string('exercise_name_snapshot');
            $table->string('measurement_type_snapshot', 30);
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('planned_sets');
            $table->unsignedSmallInteger('target_reps')->nullable();
            $table->unsignedInteger('target_duration_seconds')->nullable();
            $table->decimal('target_load_kg', 8, 2)->unsigned()->nullable();
            $table->unsignedInteger('rest_seconds')->nullable();
            $table->timestamps();

            $table->unique(['workout_session_id', 'position']);
            $table->index(['workout_session_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_exercises');
    }
};
