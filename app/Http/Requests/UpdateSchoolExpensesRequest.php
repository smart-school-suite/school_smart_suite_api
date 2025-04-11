<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolExpensesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expenses_category_id' => 'sometimes|nullable|string|exists:school_expenses_category,id',
            'date' => 'sometimes|nullable|date',
            'amount' => 'sometimes|nullable',
            'description' => 'sometimes|string'
        ];
    }
}
