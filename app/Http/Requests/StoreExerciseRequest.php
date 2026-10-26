<?php

namespace App\Http\Requests;

class StoreExerciseRequest extends ExerciseFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
