<?php

namespace App\Http\Requests\ElectionRole;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string|max:150',
            'election_type_id' => 'sometimes|nullable|string|exists:election_types,id',
            'description' => 'sometimes|nullable|string|max:2000',
            'seats' => 'sometimes|nullable|integer|min:1|max:50',
            'office_term' => 'nullable|array',
            'office_term.duration_academic_years' => 'required_with:office_term|integer|min:1|max:10',
            'office_term.allow_reelection' => 'nullable|boolean',
            'office_term.maximum_terms' => 'nullable|integer|min:1|max:20',
        ];
    }
}
