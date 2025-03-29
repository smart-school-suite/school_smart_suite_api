<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpecailtyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
  //  {
       // return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'specialty_name' => 'required|string',
            'department_id' => 'required|string',
            'registration_fee' => 'required|decimal:0, 2',
            'school_fee' => 'required|decimal:0, 2',
            'level_id' => 'required|string',
            'description' => 'sometimes|nullable|string',
        ];
    }
}
