<?php

namespace App\Http\Requests\GradesCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradesCategoryRequest extends FormRequest
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
           'title' => 'sometimes|string|max:255',
        ];
    }
}
