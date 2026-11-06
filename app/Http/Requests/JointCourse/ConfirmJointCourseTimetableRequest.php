<?php

namespace App\Http\Requests\JointCourse;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmJointCourseTimetableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Options picked from the generated proposal; max matches SaveJointCourseSlotsRequest.
            'option_ids' => 'required|array|min:1|max:20',
            'option_ids.*' => 'required|string|distinct',
        ];
    }
}
