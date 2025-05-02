<?php

namespace App\Http\Requests\ElectionRole;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateElectionRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
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
