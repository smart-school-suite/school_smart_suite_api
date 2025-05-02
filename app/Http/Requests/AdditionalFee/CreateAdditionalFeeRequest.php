<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdditionalFeeRequest extends FormRequest
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
           'reason' => 'required|string',
           'amount' => [
                'required',
                'numeric',
                'regex:/^\d{1,6}(\.\d{1,2})?$/',
                'min:0',
            ],
           'specialty_id' => 'required|string|exists:specialty,id',
           'level_id' => 'required|string|exists:education_levels,id',
           'additionalfee_category_id' => 'required|string|exists:additional_fee_category,id',
           'student_id' => 'required|string|exists:student,id'
        ];
    }
}
