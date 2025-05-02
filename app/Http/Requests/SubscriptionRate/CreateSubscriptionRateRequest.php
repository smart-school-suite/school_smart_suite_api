<?php

namespace App\Http\Requests\SubscriptionRate;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRateRequest extends FormRequest
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
            'min_students' => 'required|integer',
            'max_students' => 'required|integer',
            'monthly_rate_per_student' => 'required|numeric',
            'yearly_rate_per_student' => 'required|numeric',
            'subscription_plan_id' => 'required|string|exists:subscription_plans,id',
        ];
    }
}
