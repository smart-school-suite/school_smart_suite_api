<?php

namespace App\Http\Requests\ElectionType;

use Illuminate\Foundation\Http\FormRequest;

class ElectionTypeIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'electionTypeIds' => 'required|array|min:1',
            'electionTypeIds.*.election_type_id' => 'required|string|exists:election_types,id',
        ];
    }
}
