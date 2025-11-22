<?php

namespace App\Http\Requests\ExpensesCategory;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateExpensesCategoryRequest extends FormRequest
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
             'category_expenses' => 'required|array',
             'category_expenses.*.category_id' => 'required|string|exists:expense_categories,id',
             "category_expenses.*.status" => 'sometimes|nullable|in:active,inactive'
        ];
    }
}
