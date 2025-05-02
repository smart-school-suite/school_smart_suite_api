<?php

namespace App\Http\Requests\ElectionApplication;

use Illuminate\Foundation\Http\FormRequest;

class CreateApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'manifesto' => 'required|string',
            'personal_vision' => 'required|string',
            'commitment_statement' => 'required|string',
            'election_id' => 'required|string',
            'election_role_id' => 'required|string',
            'student_id' => 'required|string',
        ];
    }
}
