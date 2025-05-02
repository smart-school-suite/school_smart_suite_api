<?php

namespace App\Http\Requests\SchoolExpenses;

use Illuminate\Foundation\Http\FormRequest;

class CreateSchoolExpensesRequest extends FormRequest
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
            'expenses_category_id' => 'required|string',
            'date' => 'required|date',
            'amount' => 'required',
            'description' => 'sometimes|string'
        ];
    }
}
