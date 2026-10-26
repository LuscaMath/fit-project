<?php

namespace App\Http\Requests;

class UpdateExerciseRequest extends ExerciseFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
