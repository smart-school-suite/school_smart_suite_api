<?php

namespace App\Http\Requests\ElectionType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_title' => 'sometimes|nullable|string|max:150',
            'description' => 'sometimes|nullable|string|max:2000',
        ];
    }
}
