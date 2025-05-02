<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateElectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'election_type_id' => 'required|string|exists:election_types,id',
            'application_start' => 'required|date_format:Y-m-d H:i:s|after_or_equal:now',
            'application_end' => 'required|date_format:Y-m-d H:i:s|after:application_start',
            'voting_start' => 'required|date_format:Y-m-d H:i:s|after_or_equal:application_end',
            'voting_end' => 'required|date_format:Y-m-d H:i:s|after:voting_start',
            'school_year' => 'required|string|regex:/^\d{4}-\d{4}$/',
        ];
    }
}
