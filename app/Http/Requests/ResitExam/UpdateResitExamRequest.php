<?php

namespace App\Http\Requests\ResitExam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResitExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_date' => [
                'sometimes',
                'date',
                'date_format:Y-m-d',
            ],
            'end_date' => [
                'sometimes',
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'max_score' => [
                'sometimes',
                'numeric',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'min:0',
                'max:999.99',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.date'          => 'The start date must be a valid date.',
            'start_date.date_format'   => 'The start date must be in YYYY-MM-DD format.',
            'end_date.date'            => 'The end date must be a valid date.',
            'end_date.date_format'     => 'The end date must be in YYYY-MM-DD format.',
            'end_date.after_or_equal'  => 'The end date cannot be earlier than the start date.',
            'max_score.numeric'        => 'The maximum score must be a number.',
            'max_score.regex'          => 'The maximum score format is invalid.',
            'max_score.min'            => 'The maximum score cannot be less than 0.',
            'max_score.max'            => 'The maximum score cannot exceed 999.99.',
        ];
    }
}
