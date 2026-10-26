<?php

namespace App\Models;

use App\Enums\Modality;
use Database\Factories\WorkoutRoutineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'primary_modality', 'description', 'is_active'])]
class WorkoutRoutine extends Model
{
    /** @use HasFactory<WorkoutRoutineFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'primary_modality' => Modality::class,
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routineExercises(): HasMany
    {
        return $this->hasMany(RoutineExercise::class)->orderBy('position');
    }

    public function workoutSessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }
}
