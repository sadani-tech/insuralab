<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'methodology_version' => ['required', 'string', 'max:30'],
            'answers' => ['required', 'array', 'min:1', 'max:20'],
            'answers.*' => ['required', 'array:question,option'],
            'answers.*.question' => ['required', 'string', 'max:100'],
            'answers.*.option' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Pilih jawaban untuk melanjutkan assessment.',
            'answers.*.question.required' => 'Kunci pertanyaan diperlukan.',
            'answers.*.option.required' => 'Pilih satu jawaban untuk setiap pertanyaan.',
            'methodology_version.required' => 'Versi metodologi diperlukan.',
        ];
    }
}
