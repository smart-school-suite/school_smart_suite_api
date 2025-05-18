<?php

namespace App\Http\Requests\SchoolExpenses;

use Illuminate\Foundation\Http\FormRequest;

class ExpensesIdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expenseIds' => 'required|array',
            'expenseIds.*.expense_id' => 'required|string|exists:school_expenses,id'
        ];
    }
}
