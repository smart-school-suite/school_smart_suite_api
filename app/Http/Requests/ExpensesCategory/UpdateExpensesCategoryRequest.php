<?php

namespace App\Http\Requests\ExpensesCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpensesCategoryRequest extends FormRequest
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
            "name" => "sometimes|nullable|string",
            "status" => 'sometimes|nullable|in:active,inactive'
        ];
    }
}
