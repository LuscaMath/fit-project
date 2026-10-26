<?php

namespace App\Http\Requests;

use App\Models\SessionExercise;
use App\Models\WorkoutSession;
use Illuminate\Support\Facades\Gate;

class StorePerformedSetRequest extends PerformedSetRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');
        $sessionExercise = $this->route('sessionExercise');

        return $session instanceof WorkoutSession
            && $sessionExercise instanceof SessionExercise
            && $sessionExercise->workout_session_id === $session->id
            && Gate::allows('manageSets', $session);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'set_number' => ['required', 'integer', 'min:1', 'max:65535'],
            'client_request_id' => ['required', 'uuid'],
        ]);
    }

    protected function withValidator($validator): void
    {
        parent::withValidator($validator);

        $validator->after(function ($validator): void {
            $sessionExercise = $this->route('sessionExercise');
            $clientRequestId = $this->input('client_request_id');
            $setNumber = $this->input('set_number');

            if (! $sessionExercise instanceof SessionExercise || ! $clientRequestId || ! $setNumber) {
                return;
            }

            $sameRequest = $sessionExercise->performedSets()
                ->where('client_request_id', $clientRequestId)
                ->first();
            $sameNumber = $sessionExercise->performedSets()
                ->where('set_number', $setNumber)
                ->first();

            if ($sameNumber && (! $sameRequest || $sameNumber->id !== $sameRequest->id)) {
                $validator->errors()->add('set_number', 'Este número de série já foi registrado para o exercício.');
            }
        });
    }
}
