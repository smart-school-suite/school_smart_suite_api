<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateGradesCategoryRequest extends FormRequest
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
            'grades_category' => 'required|array',
            'grades_category.*.status' => 'sometimes|nullable|string',
            'grades_category.*.id' => 'required|exists:grades_category,id',
            'grades_category.*.title' => 'sometimes|string|max:255',
        ];
    }
}
