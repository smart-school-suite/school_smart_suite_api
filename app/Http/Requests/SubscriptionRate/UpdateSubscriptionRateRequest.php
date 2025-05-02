<?php

namespace App\Http\Requests\SubscriptionRate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRateRequest extends FormRequest
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
            'min_students' => 'sometimes|integer',
            'max_students' => 'sometimes|integer',
            'monthly_rate_per_student' => 'sometimes|numeric',
            'yearly_rate_per_student' => 'sometimes|numeric',
            'subscription_plan_id' => 'sometimes|string|exists:subscription_plans,id',
        ];
    }
}
