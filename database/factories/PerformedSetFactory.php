<?php

namespace Database\Factories;

use App\Models\PerformedSet;
use App\Models\SessionExercise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PerformedSet> */
class PerformedSetFactory extends Factory
{
    protected $model = PerformedSet::class;

    public function definition(): array
    {
        return [
            'session_exercise_id' => SessionExercise::factory(),
            'set_number' => fake()->unique()->numberBetween(1, 20),
            'repetitions' => 10,
            'duration_seconds' => null,
            'load_kg' => 20,
            'client_request_id' => (string) Str::uuid(),
            'completed_at' => now(),
        ];
    }
}
