<?php

namespace App\Http\Requests\ResitResult;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishResitResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resit_exam_id' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'scheduled'])],
            'published_at' => ['nullable', 'date', 'required_if:status,scheduled', 'after_or_equal:now'],
        ];
    }
}
