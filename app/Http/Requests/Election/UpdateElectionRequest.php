<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionRequest extends FormRequest
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
            'election_type_id' => 'sometimes|nullable|string|exists:election_types,id',
            'application_start' => 'sometimes|nullable|date_format:Y-m-d H:i:s|after_or_equal:now',
            'application_end' => 'sometimes|nullable|date_format:Y-m-d H:i:s|after:application_start',
            'voting_start' => 'sometimes|nullable|date_format:Y-m-d H:i:s|after_or_equal:application_end',
            'voting_end' => 'sometimes|nullable|date_format:Y-m-d H:i:s|after:voting_start',
            'school_year' => 'sometimes|nullable|string|regex:/^\d{4}-\d{4}$/',
        ];
    }
}
