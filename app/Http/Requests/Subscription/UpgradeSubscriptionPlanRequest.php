<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class UpgradeSubscriptionPlanRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "new_plan_id" => 'required|string|exists:plans,id',
            'promo_code' => 'sometimes|nullable|string|exists:affiliates,promo_code',
            'payment_method' => 'required|string',
        ];
    }
}
