<?php

namespace App\Http\Requests\TuitionFee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTuitionFeeScheduleRequest extends FormRequest
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
            'title' => 'sometimes|string',
            'num_installments' => 'sometimes|integer',
            'amount' => 'sometimes|integer',
            'due_date' => 'sometimes|date',
            'type' => 'sometimes|string',
        ];
    }
}
