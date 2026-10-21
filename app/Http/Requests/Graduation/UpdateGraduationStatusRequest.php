<?php

namespace App\Http\Requests\Graduation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGraduationStatusRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'admin_note' => 'string|max:5000',
            'record_id' => "uuid|exists:graduation_records,id"
        ];
    }
}
