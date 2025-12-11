<?php

namespace App\Http\Requests\SubscriptionPlan;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlanRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => 'required|string|max:150',
            'name' => 'required|string|max:150',
            'price' => 'required|decimal:1,1000000',
            'description' => 'required|string|max:500',
            'country_id' => 'required|string|exists:countries,id'
        ];
    }
}
