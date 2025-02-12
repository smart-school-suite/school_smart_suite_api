<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  //  public function authorize(): bool
  //  {
  //      return false;
  //  }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'manifesto' => 'sometimes|string',
            'personal_vision' => 'sometimes|string',
            'commitment_statement' => 'sometimes|string',
            'election_id' => 'sometimes|string',
            'election_role_id' => 'sometimes|string',
            'student_id' => 'sometimes|string',
        ];
    }
}
