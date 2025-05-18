<?php

namespace App\Http\Requests\ElectionRole;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionRoleRequest extends FormRequest
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
             'name' => 'sometimes|nullable|string',
             'election_type_id' => 'required|string|exists:election_type,id',
             'description' => 'sometimes|string'
        ];
    }
}
