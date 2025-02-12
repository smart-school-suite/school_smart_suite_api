<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
   // {
   //     return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string',
            'election_start_date' => 'sometimes|date',
            'election_end_date' => 'sometimes|date|after:election_start_date',
            'starting_time' => 'sometimes|date_format:H:i',
            'ending_time' => 'sometimes|date_format:H:i|after:starting_time',
            'description' => 'sometimes|string'
        ];
    }
}
