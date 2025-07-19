<?php

namespace App\Http\Requests\AdditionalFeeCategory;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateAdditionalFeeCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
             'fee_category' => 'required|array',
             'fee_category.*.id' => 'required|string|exists:additional_fee_category,id',
             'fee_category.*.title' => 'sometimes|nullable|string',
             'fee_category.*.status' => 'sometimes|nullable|in:active,inactive'
        ];
    }
}
