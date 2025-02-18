<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HosRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
    //{
      //  return false;
    //}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'specialty_id' => 'required|string|exists:specialty,id',
            'hodable_id' => 'required|string',
        ];
    }
}
