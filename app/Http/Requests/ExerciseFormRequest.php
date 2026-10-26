<?php

namespace App\Http\Requests;

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Support\VideoUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ExerciseFormRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'modality' => ['required', 'string', Rule::enum(Modality::class)],
            'measurement_type' => ['required', 'string', Rule::enum(MeasurementType::class)],
            'muscle_group' => ['required', 'string', 'max:80'],
            'equipment' => ['nullable', 'string', 'max:80'],
            'instructions' => ['required', 'string', 'max:10000'],
            'video_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $url = $this->input('video_url');

            if ($url !== null && $url !== '' && ! VideoUrl::isAllowed($url)) {
                $validator->errors()->add('video_url', 'Informe um link do YouTube ou Vimeo.');
            }
        });
    }
}
