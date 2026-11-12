<?php

namespace App\Http\Requests\ElectionType;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateElectionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_types' => 'required|array|min:1',
            'election_types.*.election_type_id' => 'required|string|exists:election_types,id',
            'election_types.*.election_title' => 'sometimes|nullable|string|max:150',
            'election_types.*.description' => 'sometimes|nullable|string|max:2000',
            'election_types.*.status' => 'sometimes|nullable|in:active,inactive',
        ];
    }
}
