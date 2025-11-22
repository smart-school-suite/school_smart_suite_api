<?php

namespace App\Http\Requests\AdditionalFeeCategory;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdditionalFeeCategoryRequest extends FormRequest
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
            'title' => 'required|string|max:150',
        ];
    }
}
