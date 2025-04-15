<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateElectionRolesRequest extends FormRequest
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
            'election_roles' => 'required|array',
            'election_roles.*.election_role_id' => 'required|array|exists:table,column',
            'election_roles.*.name' => 'sometimes|nullable|string',
            'election_roles.*.election_type_id' => 'sometimes|nullable|exists:election_type,id',
            'election_roles.*.description' => 'sometimes|nullable|string'
        ];
    }
}
