<?php

namespace App\Http\Requests\SchoolExpenses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolExpensesRequest extends FormRequest
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
            'expenses_category_id' => 'sometimes|nullable|string|exists:school_expenses_category,id',
            'date' => 'sometimes|nullable|date',
            'amount' => 'sometimes|nullable',
            'description' => 'sometimes|nullable|string'
        ];
    }
}
