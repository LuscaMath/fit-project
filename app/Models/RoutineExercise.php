<?php

namespace App\Models;

use Database\Factories\RoutineExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workout_routine_id',
    'exercise_id',
    'position',
    'planned_sets',
    'target_reps',
    'target_duration_seconds',
    'target_load_kg',
    'rest_seconds',
    'notes',
])]
class RoutineExercise extends Model
{
    /** @use HasFactory<RoutineExerciseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'target_load_kg' => 'decimal:2',
        ];
    }

    public function workoutRoutine(): BelongsTo
    {
        return $this->belongsTo(WorkoutRoutine::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
