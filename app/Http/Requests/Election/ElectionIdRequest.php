<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class ElectionIdRequest extends FormRequest
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
            'electionIds' => 'required|array',
            'electionIds.*.election_id' => 'required|string|exists:elections,id'
        ];
    }
}
