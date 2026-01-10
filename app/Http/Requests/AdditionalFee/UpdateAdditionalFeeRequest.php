<?php

namespace App\Http\Requests\AdditionalFee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdditionalFeeRequest extends FormRequest
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
            'title' => 'sometimes|nullable|string',
            'reason' => 'sometimes|nullable|string',
            'amount' => [
                'sometimes',
                'nullable',
                'numeric',
                'regex:/^\d{1,6}(\.\d{1,2})?$/',
                'min:0',
            ],
            'due_date' => 'sometimes|nullable|date|after_or_equal:today'
        ];
    }
}
