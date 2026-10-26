<?php

namespace App\Models;

use App\Enums\Modality;
use App\Enums\WorkoutSessionStatus;
use Database\Factories\WorkoutSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'workout_routine_id',
    'routine_name_snapshot',
    'primary_modality_snapshot',
    'status',
    'started_at',
    'finished_at',
])]
class WorkoutSession extends Model
{
    /** @use HasFactory<WorkoutSessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'primary_modality_snapshot' => Modality::class,
            'status' => WorkoutSessionStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutRoutine(): BelongsTo
    {
        return $this->belongsTo(WorkoutRoutine::class);
    }

    public function sessionExercises(): HasMany
    {
        return $this->hasMany(SessionExercise::class)->orderBy('position');
    }
}
