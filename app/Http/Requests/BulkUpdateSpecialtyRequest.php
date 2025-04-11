<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSpecialtyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'specialties' => 'required|array',
            'specialties.*.id' => 'required|string|exists:specialty,id',
            'specialties.*.specialty_name' => 'sometimes|nullable|string',
            'specialties.*.registration_fee' => 'sometimes|nullable',
            'specialties.*.school_fee' => 'sometimes|nullable',
            'specialties.*.level_id' => 'sometimes|nullable|string|exists:education_levels,id',
            'specialties.*.department_id' => 'sometimes|nullable|string|exists:department,id',
            'specialties.*.description' => 'sometimes|nullable|string',
        ];
    }
}
