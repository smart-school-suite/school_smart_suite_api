<?php

namespace App\Http\Requests\GraduationBatch;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGraduationBatchRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                  => ['nullable', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'graduation_start_date' => ['nullable', 'date', 'date_format:Y-m-d H:i'],
            'graduation_end_date'   => [
                'nullable',
                'date',
                'date_format:Y-m-d H:i',
                'after_or_equal:graduation_start_date',
            ],
            'location'             => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'graduation_end_date.after_or_equal' => 'The graduation end date must be a date after or equal to the start date.',
            'graduation_start_date.date_format'  => 'The start date must match the format YYYY-MM-DD.',
            'graduation_end_date.date_format'    => 'The end date must match the format YYYY-MM-DD.',
        ];
    }
}
