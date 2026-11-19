<?php

namespace App\Http\Requests\AdditionalFee;

use Illuminate\Foundation\Http\FormRequest;

class BulkBillStudentAdditionalFeeRequest extends FormRequest
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
            'fee_details' => 'required|array|min:1',
            'fee_details.*.student_id' => 'required|string|distinct|exists:students,id',
            'fee_details.*.reason' => 'required|string|max:1000',
            'fee_details.*.additionalfee_category_id' => 'required|string|exists:additional_fee_categories,id',
             'fee_details.*.amount' => [
                'required',
                'numeric',
                'regex:/^\d{1,6}(\.\d{1,2})?$/',
                'min:0',
            ],
            // Left out: the billing day plus the default due period setting.
            'fee_details.*.due_date' => 'sometimes|nullable|date|after_or_equal:today'
        ];
    }
}
