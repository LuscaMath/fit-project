<?php

namespace App\Http\Requests;

use App\Models\PerformedSet;
use App\Models\WorkoutSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DeletePerformedSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');
        $performedSet = $this->route('performedSet');

        return $session instanceof WorkoutSession
            && $performedSet instanceof PerformedSet
            && $performedSet->sessionExercise->workout_session_id === $session->id
            && Gate::allows('manageSets', $session);
    }
}
