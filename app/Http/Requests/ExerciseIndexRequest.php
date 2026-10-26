<?php

namespace App\Http\Requests;

use App\Enums\Modality;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExerciseIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'modality' => ['nullable', 'string', Rule::enum(Modality::class)],
            'muscle_group' => ['nullable', 'string', 'max:80'],
            'equipment' => ['nullable', 'string', 'max:80'],
        ];
    }
}
