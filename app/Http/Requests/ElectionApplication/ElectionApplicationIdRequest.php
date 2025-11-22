<?php

namespace App\Http\Requests\ElectionApplication;

use Illuminate\Foundation\Http\FormRequest;

class ElectionApplicationIdRequest extends FormRequest
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
            'electionApplicationIds' => 'required|array',
            "electionApplicationIds.*.election_application_id" => 'required|string|exists:election_applications,id'
        ];
    }
}
