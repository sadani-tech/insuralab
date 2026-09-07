<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1', 'max:20'],
            'answers.*' => ['required', 'array:question,option'],
            'answers.*.question' => ['required', 'string', 'max:100'],
            'answers.*.option' => ['required', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'answers.required' => 'Pilih jawaban untuk menyelesaikan quiz.',
            'answers.*.question.required' => 'Kunci pertanyaan diperlukan.',
            'answers.*.option.required' => 'Pilih satu jawaban untuk setiap pertanyaan.',
        ];
    }
}
