<?php

namespace App\Http\Requests\ElectionType;

use Illuminate\Foundation\Http\FormRequest;

class CreateElectionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_title' => 'required|string|max:150',
            'description' => 'required|string|max:2000',
        ];
    }
}
