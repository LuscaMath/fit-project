<?php

namespace App\Http\Requests;

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Models\Exercise;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class WorkoutRoutineFormRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'primary_modality' => ['required', 'string', Rule::enum(Modality::class)],
            'description' => ['nullable', 'string', 'max:10000'],
            'exercises' => ['required', 'array', 'min:1', 'max:100'],
            'exercises.*.position' => ['required', 'integer', 'min:1', 'max:100'],
            'exercises.*.exercise_id' => ['required', 'integer', 'exists:exercises,id'],
            'exercises.*.planned_sets' => ['required', 'integer', 'min:1', 'max:100'],
            'exercises.*.target_reps' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'exercises.*.target_duration_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'exercises.*.target_load_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'exercises.*.rest_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'exercises.*.notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $items = $this->input('exercises', []);
            $ids = collect($items)->pluck('exercise_id')->filter()->unique()->values();
            $visibleExercises = Exercise::query()
                ->whereIn('id', $ids)
                ->where(function ($query): void {
                    $query->whereNull('user_id')->orWhere('user_id', $this->user()->id);
                })
                ->get()
                ->keyBy('id');

            foreach ($items as $index => $item) {
                $exercise = $visibleExercises->get((int) ($item['exercise_id'] ?? 0));

                if (! $exercise) {
                    $validator->errors()->add("exercises.{$index}.exercise_id", 'Selecione um exercício disponível para sua conta.');

                    continue;
                }

                $hasReps = filled($item['target_reps'] ?? null);
                $hasDuration = filled($item['target_duration_seconds'] ?? null);

                if ($exercise->measurement_type === MeasurementType::Repetitions && ! $hasReps) {
                    $validator->errors()->add("exercises.{$index}.target_reps", 'Informe a meta de repetições para este exercício.');
                }

                if ($exercise->measurement_type === MeasurementType::Duration && ! $hasDuration) {
                    $validator->errors()->add("exercises.{$index}.target_duration_seconds", 'Informe a meta de duração para este exercício.');
                }

                if ($exercise->measurement_type === MeasurementType::Repetitions && $hasDuration) {
                    $validator->errors()->add("exercises.{$index}.target_duration_seconds", 'Este exercício usa meta de repetições.');
                }

                if ($exercise->measurement_type === MeasurementType::Duration && $hasReps) {
                    $validator->errors()->add("exercises.{$index}.target_reps", 'Este exercício usa meta de duração.');
                }

                if ($exercise->measurement_type === MeasurementType::RepetitionsAndDuration && ! $hasReps && ! $hasDuration) {
                    $validator->errors()->add("exercises.{$index}.target_reps", 'Informe repetições ou duração para este exercício.');
                }
            }
        });
    }
}
