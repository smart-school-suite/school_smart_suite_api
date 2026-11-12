<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Election\Concerns\ValidatesElectionParticipants;

class CreateElectionRequest extends FormRequest
{
    use ValidatesElectionParticipants;

    protected function prepareForValidation(): void
    {
        // Backwards compatibility with the previous `school_year_id` field.
        if (!$this->filled('system_academic_year_id') && $this->filled('school_year_id')) {
            $this->merge(['system_academic_year_id' => $this->input('school_year_id')]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:2000',
            'election_type_id' => 'required|string|exists:election_types,id',
            'system_academic_year_id' => 'required|string|exists:system_academic_years,id',
            'application_start' => 'required|date_format:Y-m-d H:i|after_or_equal:now',
            'application_end' => 'required|date_format:Y-m-d H:i|after:application_start',
            'voting_start' => 'required|date_format:Y-m-d H:i|after_or_equal:application_end',
            'voting_end' => 'required|date_format:Y-m-d H:i|after:voting_start',
            'max_applications_per_participant' => 'nullable|integer|min:1|max:10',
            'allow_candidate_self_vote' => 'nullable|boolean',
            'participants' => 'nullable|array',
        ], $this->participantRules('participants.'));
    }
}
