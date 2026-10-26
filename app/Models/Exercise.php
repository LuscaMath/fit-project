<?php

namespace App\Models;

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Support\VideoUrl;
use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'modality',
    'measurement_type',
    'muscle_group',
    'equipment',
    'instructions',
    'video_url',
])]
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'modality' => Modality::class,
            'measurement_type' => MeasurementType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routineExercises(): HasMany
    {
        return $this->hasMany(RoutineExercise::class);
    }

    public function sessionExercises(): HasMany
    {
        return $this->hasMany(SessionExercise::class);
    }

    public function videoEmbedUrl(): ?string
    {
        return VideoUrl::embedUrl($this->video_url);
    }
}
