<?php

namespace App\Http\Requests\SchoolExpenses;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSchoolExpensesRequest extends FormRequest
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
            'school_expenses' => 'required|array',
            'school_expenses.*.expense_id' => 'required|string|exists:expense_categories,id',
            'school_expenses.*.expenses_category_id' => 'sometimes|nullable|string|exists:expense_categories,id',
            'school_expenses.*.date' => 'sometimes|nullable|date',
            'school_expenses.*.amount' => 'sometimes|nullable|required',
            'school_expenses.*.description' => 'sometimes|nullable|string'
        ];
    }
}
