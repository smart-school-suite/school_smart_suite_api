<?php

namespace App\Http\Requests\ElectionType;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateElectionTypeRequest extends FormRequest
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
            'election_types' => 'required|array',
            'election_types.*.election_type_id' => 'required|string|exists:election_types,id',
            'election_types.*.status' => 'sometimes|nullable|string',
            'election_types.*.election_title' => 'sometimes|nullable|string',
            'election_types.*.description' => 'sometimes|nullable|string'
        ];
    }
}
