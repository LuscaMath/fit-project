<?php

namespace Database\Factories;

use App\Enums\MeasurementType;
use App\Models\Exercise;
use App\Models\SessionExercise;
use App\Models\WorkoutSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SessionExercise> */
class SessionExerciseFactory extends Factory
{
    protected $model = SessionExercise::class;

    public function definition(): array
    {
        return [
            'workout_session_id' => WorkoutSession::factory(),
            'exercise_id' => Exercise::factory(),
            'exercise_name_snapshot' => fake()->words(2, true),
            'measurement_type_snapshot' => MeasurementType::Repetitions,
            'position' => fake()->unique()->numberBetween(1, 50),
            'planned_sets' => 3,
            'target_reps' => 10,
            'target_duration_seconds' => null,
            'target_load_kg' => 20,
            'rest_seconds' => 90,
        ];
    }
}
