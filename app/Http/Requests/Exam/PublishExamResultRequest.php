<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishExamResultRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exam_id' => [
                'required',
                'string',
                'uuid',
                'exists:exams,id',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'scheduled']),
            ],
            'published_at' => [
                'nullable',
                'date',
                'required_if:status,scheduled',
                'after_or_equal:now',
            ],
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'published_at.required_if' => 'A publication date is required when scheduling result releases.',
            'published_at.after_or_equal' => 'The scheduled publication date cannot be set in the past.',
            'exam_id.exists' => 'The selected exam does not exist in the database.',
        ];
    }
}
