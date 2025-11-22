<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class AddElectionParticipantsRequest extends FormRequest
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
            'election_participants' => 'required|array',
           'election_participants.*.specialty_id' => 'required|exists:specialties,id',
           'election_participants.*.election_id' => 'required|exists:elections,id',
           'election_participants.*.level_id' => 'required|exists:levels,id'
        ];
    }
}
