<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateLetterGradeRequest extends FormRequest
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
            'letter_grades' => 'required|array',
            'letter_grades.*.letter_grade' => 'sometimes|nullable|string|max:10',
            'letter_grades.*.status' => 'sometimes|nullable|in:active,inactive'
        ];
    }
}
