<?php

namespace App\Models;

use App\Enums\MeasurementType;
use Database\Factories\SessionExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'workout_session_id',
    'exercise_id',
    'exercise_name_snapshot',
    'measurement_type_snapshot',
    'position',
    'planned_sets',
    'target_reps',
    'target_duration_seconds',
    'target_load_kg',
    'rest_seconds',
])]
class SessionExercise extends Model
{
    /** @use HasFactory<SessionExerciseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'measurement_type_snapshot' => MeasurementType::class,
            'target_load_kg' => 'decimal:2',
        ];
    }

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function performedSets(): HasMany
    {
        return $this->hasMany(PerformedSet::class)->orderBy('set_number');
    }
}
