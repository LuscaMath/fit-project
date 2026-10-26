<?php

namespace App\Http\Requests;

use App\Models\WorkoutRoutine;

class StoreWorkoutRoutineRequest extends WorkoutRoutineFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', WorkoutRoutine::class) ?? false;
    }
}
