<?php

namespace App\Http\Requests\ElectionApplication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionApplicationRequest extends FormRequest
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
            'manifesto' => 'sometimes|string',
            'personal_vision' => 'sometimes|string',
            'commitment_statement' => 'sometimes|string',
            'election_id' => 'sometimes|string|exists:elections,id',
            'election_role_id' => 'sometimes|string|exists:election_roles,id',
            'student_id' => 'sometimes|string|exists:students,id',
        ];
    }
}
