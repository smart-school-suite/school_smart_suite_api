<?php

namespace App\Http\Requests\LetterGrade;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLetterGradeRequest extends FormRequest
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
           'letter_grade' => 'sometimes|nullable|string|max:10',
           'status' => 'sometimes|nullable|in:active,inactive'
        ];
    }
}
