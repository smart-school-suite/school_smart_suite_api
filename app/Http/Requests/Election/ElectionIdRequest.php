<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class ElectionIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'electionIds' => 'required|array|min:1',
            'electionIds.*.election_id' => 'required|string|exists:elections,id',
        ];
    }
}
