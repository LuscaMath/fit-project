<?php

namespace App\Http\Requests;

use App\Models\WorkoutSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class FinishWorkoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session instanceof WorkoutSession
            && Gate::allows('finish', $session);
    }

    public function rules(): array
    {
        return [];
    }
}
