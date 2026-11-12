<?php

namespace App\Http\Requests\ElectionOffice;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceOfficeHolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'holder_type' => 'required|in:student,teacher,school_admin',
            'holder_id' => 'required|string',
            'ended_at' => 'nullable|date_format:Y-m-d',
            'termination_remarks' => 'nullable|string|max:2000',
            'remarks' => 'nullable|string|max:2000',
        ];
    }
}
