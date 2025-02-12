<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ElectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
    //{
   ///     return false;
    //}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'election_start_date' => 'required|date',
            'election_end_date' => 'required|date|after:election_start_date',
            'starting_time' => 'required|date_format:H:i',
            'ending_time' => 'required|date_format:H:i|after:starting_time',
            'description' => 'required|string'
        ];
    }
}
