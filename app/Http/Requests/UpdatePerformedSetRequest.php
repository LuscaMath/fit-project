<?php

namespace App\Http\Requests;

use App\Models\PerformedSet;
use App\Models\WorkoutSession;
use Illuminate\Support\Facades\Gate;

class UpdatePerformedSetRequest extends PerformedSetRequest
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
