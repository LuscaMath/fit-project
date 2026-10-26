<?php

namespace App\Http\Requests;

use App\Enums\MeasurementType;
use App\Models\PerformedSet;
use App\Models\SessionExercise;
use Illuminate\Foundation\Http\FormRequest;

abstract class PerformedSetRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'repetitions' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'load_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $sessionExercise = $this->sessionExercise();

            if (! $sessionExercise instanceof SessionExercise) {
                return;
            }

            $hasRepetitions = filled($this->input('repetitions'));
            $hasDuration = filled($this->input('duration_seconds'));
            $measurementType = $sessionExercise->measurement_type_snapshot;

            if ($measurementType === MeasurementType::Repetitions && ! $hasRepetitions) {
                $validator->errors()->add('repetitions', 'Informe as repetições desta série.');
            }

            if ($measurementType === MeasurementType::Duration && ! $hasDuration) {
                $validator->errors()->add('duration_seconds', 'Informe a duração desta série.');
            }

            if ($measurementType === MeasurementType::RepetitionsAndDuration && ! $hasRepetitions && ! $hasDuration) {
                $validator->errors()->add('repetitions', 'Informe repetições ou duração desta série.');
            }

            if ($measurementType === MeasurementType::Repetitions && $hasDuration) {
                $validator->errors()->add('duration_seconds', 'Este exercício aceita apenas repetições.');
            }

            if ($measurementType === MeasurementType::Duration && $hasRepetitions) {
                $validator->errors()->add('repetitions', 'Este exercício aceita apenas duração.');
            }
        });
    }

    protected function sessionExercise(): ?SessionExercise
    {
        $sessionExercise = $this->route('sessionExercise');

        if ($sessionExercise instanceof SessionExercise) {
            return $sessionExercise;
        }

        $performedSet = $this->route('performedSet');

        return $performedSet instanceof PerformedSet ? $performedSet->sessionExercise : null;
    }
}
