<?php

namespace App\Http\Requests\ElectionType;

use Illuminate\Foundation\Http\FormRequest;

class ElectionTypeIdRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'electionTypeIds' => 'required|array',
            'electionTypeIds.*.election_type_id' => 'required|string|exists:election_types,id'
        ];
    }
}
