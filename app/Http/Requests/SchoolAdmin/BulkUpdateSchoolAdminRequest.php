<?php

namespace App\Http\Requests\SchoolAdmin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSchoolAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'school_admins' => 'required|array',
            'school_admins.*.id' => 'required|string|exists:school_admins,id',
            'school_admins.*.email' => 'sometimes|nullable|email',
            'school_admins.*.name' => 'sometimes|nullable|string',
            'school_admins.*.first_name' => 'sometimes|nullable|string',
            'school_admins.*.last_name' => 'sometimes|nullable|string',
        ];
    }
}
