<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamTypeRequest extends FormRequest
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
            'semester_id' => 'sometimes|string',
            'exam_name' => 'sometimes|string',
            'status' => 'sometimes|string|in:active,inactive',
            'program_name' => 'sometimes|string',
        ];
    }
}
