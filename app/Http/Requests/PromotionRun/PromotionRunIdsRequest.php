<?php

namespace App\Http\Requests\PromotionRun;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRunIdsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'promotion_run_ids' => ['required', 'array', 'min:1'],
            'promotion_run_ids.*' => ['required', 'string', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return ['promotion_run_ids.required' => 'Select at least one promotion run.'];
    }
}
