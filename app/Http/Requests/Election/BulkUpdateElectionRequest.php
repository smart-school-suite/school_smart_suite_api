<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateElectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'elections' => 'required|array',
            'elections.*.election_type_id' => 'required|string|exists:election_types,id',
            'elections.*.application_start' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:now',
            'elections.*.application_end' => 'nullable|date_format:Y-m-d H:i:s|after:elections.*.application_start',
            'elections.*.voting_start' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:elections.*.application_end',
            'elections.*.voting_end' => 'nullable|date_format:Y-m-d H:i:s|after:elections.*.voting_start',
            'elections.*.school_year' => 'nullable|string|regex:/^\d{4}-\d{4}$/',
        ];
    }
}
