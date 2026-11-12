<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|nullable|string|max:150',
            'description' => 'sometimes|nullable|string|max:2000',
            'election_type_id' => 'sometimes|nullable|string|exists:election_types,id',
            'system_academic_year_id' => 'sometimes|nullable|string|exists:system_academic_years,id',
            'application_start' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'application_end' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'voting_start' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'voting_end' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'max_applications_per_participant' => 'sometimes|nullable|integer|min:1|max:10',
            'allow_candidate_self_vote' => 'sometimes|nullable|boolean',
        ];
    }
}
