<?php

namespace App\Http\Requests\ExamScore;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateExamScoreRequest extends FormRequest
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
            // Define your bulk update validation rules here
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:your_table,id',
            // 'data.*.field_name' => 'sometimes|string|max:255',
        ];
    }
}
