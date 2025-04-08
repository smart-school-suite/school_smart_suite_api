<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGuardianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
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
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_one' => 'required|string|max:15',
            'occupation' => 'required|string|max:255',
            'relationship_to_student' => 'required|string|max:255',
            'preferred_language' => 'required|string|max:50',
        ];
    }
}
