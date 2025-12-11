<?php

namespace App\Http\Requests\SubscriptionPlan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeatureRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string|max:150',
            'description' => 'sometimes|nullable|string|max:1000',
            'key' => 'sometimes|nullable|string|max:100',
            'country_id' => 'sometimes|nullable|string|exists:countries,id'
        ];
    }
}
