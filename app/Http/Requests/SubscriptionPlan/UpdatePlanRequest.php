<?php

namespace App\Http\Requests\SubscriptionPlan;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => 'sometimes|nullable|string|max:150',
            'name' => 'sometimes|nullable|string|max:150',
            'price' => 'sometimes|nullable|decimal:1,1000000',
            'description' => 'sometimes|nullable|string|max:500',
            'country_id' => 'sometimes|nullable|string|exists:countries,id'
        ];
    }
}
