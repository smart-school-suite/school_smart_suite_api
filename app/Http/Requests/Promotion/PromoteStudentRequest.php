<?php

namespace App\Http\Requests\Promotion;

use Illuminate\Foundation\Http\FormRequest;

class PromoteStudentRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'record_id' => 'uuid|exists:student_promotion_records,id',
            'admin_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
