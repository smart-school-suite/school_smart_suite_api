<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class CreateVoteRequest extends FormRequest
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
            'candidate_id' => 'required|string',
            'student_id' => 'required|string',
            'election_id' => 'required|string',
            'position_id' => 'required|string'
        ];
    }
}
