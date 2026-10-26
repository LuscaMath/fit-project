<?php

namespace Database\Factories;

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Exercise> */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->words(2, true),
            'modality' => Modality::Gym,
            'measurement_type' => MeasurementType::Repetitions,
            'muscle_group' => fake()->randomElement(['chest', 'back', 'legs', 'shoulders']),
            'equipment' => fake()->randomElement(['barbell', 'dumbbell', 'bodyweight']),
            'instructions' => fake()->paragraph(),
            'video_url' => null,
        ];
    }

    public function personal(User|int $user): static
    {
        return $this->state([
            'user_id' => $user instanceof User ? $user->getKey() : $user,
        ]);
    }

    public function global(): static
    {
        return $this->state(['user_id' => null]);
    }
}
