<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RateCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
   // {
   //     return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
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
