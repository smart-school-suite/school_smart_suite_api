<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateElectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'elections' => 'required|array|min:1',
            'elections.*.election_id' => 'required|string|exists:elections,id',
            'elections.*.title' => 'sometimes|nullable|string|max:150',
            'elections.*.description' => 'sometimes|nullable|string|max:2000',
            'elections.*.election_type_id' => 'sometimes|nullable|string|exists:election_types,id',
            'elections.*.system_academic_year_id' => 'sometimes|nullable|string|exists:system_academic_years,id',
            'elections.*.application_start' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'elections.*.application_end' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'elections.*.voting_start' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'elections.*.voting_end' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'elections.*.max_applications_per_participant' => 'sometimes|nullable|integer|min:1|max:10',
            'elections.*.allow_candidate_self_vote' => 'sometimes|nullable|boolean',
        ];
    }
}
