<?php

namespace App\Http\Requests\PromotionRun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TriggerPromotionRunRequest extends FormRequest
{


    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['scheduled', 'active']),
            ],
            'promotion_run_id' => [
                'required',
                'uuid',
                'exists:promotion_runs,id',
            ],
            'initiate_at' => [
                'nullable',
                'date',
                'after_or_equal:now',
                'required_if:status,scheduled',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be either "scheduled" or "active".',
            'promotion_run_id.required' => 'The promotion run ID is required.',
            'promotion_run_id.uuid' => 'The promotion run ID must be a valid UUID.',
            'promotion_run_id.exists' => 'The specified promotion run does not exist.',
            'initiate_at.required_if' => 'The initiate at field is required when status is "scheduled".',
            'initiate_at.after_or_equal' => 'The initiate at must be a future date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->input('status')),
            ]);
        }
    }

    public function validatedData(): array
    {
        return $this->safe()->only([
            'status',
            'promotion_run_id',
            'initiate_at',
        ]);
    }
}
