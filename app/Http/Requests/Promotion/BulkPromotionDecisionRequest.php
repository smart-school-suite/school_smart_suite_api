<?php

namespace App\Http\Requests\Promotion;

use Illuminate\Foundation\Http\FormRequest;

/** Records to approve or set to repeat together, with one optional note. */
class BulkPromotionDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'record_ids' => ['required', 'array', 'min:1'],
            'record_ids.*' => ['required', 'string', 'distinct'],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
