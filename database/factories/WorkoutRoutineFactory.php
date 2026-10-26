<?php

namespace Database\Factories;

use App\Enums\Modality;
use App\Models\User;
use App\Models\WorkoutRoutine;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkoutRoutine> */
class WorkoutRoutineFactory extends Factory
{
    protected $model = WorkoutRoutine::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'primary_modality' => Modality::Gym,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function archived(): static
    {
        return $this->state(['is_active' => false]);
    }
}
