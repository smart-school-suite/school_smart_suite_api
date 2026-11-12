<?php

namespace App\Http\Requests\ElectionRole;

use Illuminate\Foundation\Http\FormRequest;

class ElectionRoleIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'electionRoleIds' => 'required|array|min:1',
            'electionRoleIds.*.election_role_id' => 'required|string|exists:election_roles,id',
        ];
    }
}
