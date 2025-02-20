<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeWaiverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
    //{
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
         'start_date' => 'sometimes|date',
         'end_date' => 'sometimes|date',
         'description' => 'sometimes|string',
        ];
    }
}
