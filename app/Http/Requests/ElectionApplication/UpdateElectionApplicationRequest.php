<?php

namespace App\Http\Requests\ElectionApplication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_role_id' => 'sometimes|nullable|string|exists:election_roles,id',
            'manifesto' => 'sometimes|nullable|string|max:10000',
            'personal_vision' => 'sometimes|nullable|string|max:5000',
            'commitment_statement' => 'sometimes|nullable|string|max:5000',
        ];
    }
}
