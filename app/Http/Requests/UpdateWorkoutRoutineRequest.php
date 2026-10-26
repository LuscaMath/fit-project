<?php

namespace App\Http\Requests;

use App\Models\WorkoutRoutine;

class UpdateWorkoutRoutineRequest extends WorkoutRoutineFormRequest
{
    public function authorize(): bool
    {
        $routine = $this->route('routine');

        return $routine instanceof WorkoutRoutine
            && ($this->user()?->can('update', $routine) ?? false);
    }
}
