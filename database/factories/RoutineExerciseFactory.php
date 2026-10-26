<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\RoutineExercise;
use App\Models\WorkoutRoutine;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoutineExercise> */
class RoutineExerciseFactory extends Factory
{
    protected $model = RoutineExercise::class;

    public function definition(): array
    {
        return [
            'workout_routine_id' => WorkoutRoutine::factory(),
            'exercise_id' => Exercise::factory(),
            'position' => fake()->unique()->numberBetween(1, 50),
            'planned_sets' => 3,
            'target_reps' => 10,
            'target_duration_seconds' => null,
            'target_load_kg' => 20,
            'rest_seconds' => 90,
            'notes' => null,
        ];
    }
}
