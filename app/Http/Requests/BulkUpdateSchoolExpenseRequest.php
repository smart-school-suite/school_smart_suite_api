<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSchoolExpenseRequest extends FormRequest
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
            'school_expenses' => 'required|array',
            'school_expenses.*.id' => 'required|string|exists:school_expenses,id',
            'school_expenses.*.expenses_category_id' => 'sometimes|nullable|string|exists:school_expenses_category,id',
            'school_expenses.*.date' => 'sometimes|nullable|date',
            'school_expenses.*.amount' => 'sometimes|nullable|required',
            'school_expenses.*.description' => 'sometimes|nullable|string'
        ];
    }
}
