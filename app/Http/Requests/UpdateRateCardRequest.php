<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRateCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
   // {
      //  return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
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
