<?php

namespace App\Http\Requests\PromotionRun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkTriggerPromotionRunRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'promotion_run_ids' => ['required', 'array', 'min:1'],
            'promotion_run_ids.*' => ['required', 'string', 'distinct'],
            'status' => ['required', 'string', Rule::in(['scheduled', 'active'])],
            'initiate_at' => ['nullable', 'date', 'after_or_equal:now', 'required_if:status,scheduled'],
        ];
    }

    public function messages(): array
    {
        return [
            'promotion_run_ids.required' => 'Select at least one promotion run.',
            'initiate_at.required_if' => 'Pick when the scheduled runs should start.',
            'initiate_at.after_or_equal' => 'The scheduled time must be in the future.',
        ];
    }
}
