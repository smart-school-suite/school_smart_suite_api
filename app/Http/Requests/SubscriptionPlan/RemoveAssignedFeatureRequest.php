<?php

namespace App\Http\Requests\SubscriptionPlan;

use Illuminate\Foundation\Http\FormRequest;

class RemoveAssignedFeatureRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_id' => 'required|string|exists:plans,id',
            'feature_ids' => 'required|array',
            'feature_ids.*.feature_id' => 'required|string|exists:features,id'
        ];
    }
}
