<?php

namespace Database\Factories;

use App\Enums\Modality;
use App\Enums\WorkoutSessionStatus;
use App\Models\User;
use App\Models\WorkoutSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkoutSession> */
class WorkoutSessionFactory extends Factory
{
    protected $model = WorkoutSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workout_routine_id' => null,
            'routine_name_snapshot' => fake()->words(3, true),
            'primary_modality_snapshot' => Modality::Gym,
            'status' => WorkoutSessionStatus::InProgress,
            'started_at' => now(),
            'finished_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => WorkoutSessionStatus::Completed,
            'finished_at' => now(),
        ]);
    }
}
