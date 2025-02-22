<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddtionalFeesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
    //{
        //return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'reason' => 'required|string',
           'amount' => 'required|integer',
           'specialty_id' => 'required|string|exists:specialty,id',
           'level_id' => 'required|string|exists:education_levels,id',
           'additionalfee_category_id' => 'required|string|exists:additional_fee_category,id',
           'student_id' => 'required|string|exists:student,id'
        ];
    }
}
