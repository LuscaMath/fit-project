<?php

namespace App\Models;

use Database\Factories\PerformedSetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'session_exercise_id',
    'set_number',
    'repetitions',
    'duration_seconds',
    'load_kg',
    'client_request_id',
    'completed_at',
])]
class PerformedSet extends Model
{
    /** @use HasFactory<PerformedSetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'load_kg' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function sessionExercise(): BelongsTo
    {
        return $this->belongsTo(SessionExercise::class);
    }
}
