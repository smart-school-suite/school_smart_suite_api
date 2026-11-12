<?php

namespace App\Http\Requests\ElectionApplication;

use Illuminate\Foundation\Http\FormRequest;

class CreateApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_id' => 'required|string|exists:elections,id',
            'election_role_id' => 'required|string|exists:election_roles,id',
            'manifesto' => 'required|string|max:10000',
            'personal_vision' => 'required|string|max:5000',
            'commitment_statement' => 'required|string|max:5000',
            // Optional: a school admin submitting on behalf of a participant.
            'applicant_type' => 'nullable|required_with:applicant_id|in:student,teacher,school_admin',
            'applicant_id' => 'nullable|required_with:applicant_type|string',
        ];
    }
}
