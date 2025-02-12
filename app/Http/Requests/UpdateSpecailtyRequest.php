<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecailtyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
    //{
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
            'specialty_name' => 'sometimes|required|string',
            'department_id' => 'sometimes|required|string',
            'registration_fee' => 'sometimes|required|decimal:0, 2',
            'school_fee' => 'sometimes|required|decimal:0, 2',
            'level_id' => 'sometimes|required|string'
        ];
    }
}
