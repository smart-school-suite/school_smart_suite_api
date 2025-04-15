<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateElectionTypeRequest extends FormRequest
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
            'election_title' => 'required|string',
            'description' => 'required|string'
        ];
    }
}
