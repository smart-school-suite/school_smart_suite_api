<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class RenewSubscriptionPlanRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // omitted: renew the current plan
            'plan_id' => 'sometimes|nullable|string|exists:plans,id',
            'payment_method_id' => 'required|string|exists:payment_method,id',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'payment_ref' => 'sometimes|nullable|string|max:150',
        ];
    }
}
