<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeePaymentScheduleRequest extends FormRequest
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
            'title' => 'required|string',
            'num_installments' => 'required|integer',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'type' => 'required|string',
            'specialty_id' => 'required|string|exists:specialty,id',
            'level_id' => 'required|string|exists:education_levels,id',
        ];
    }
}
