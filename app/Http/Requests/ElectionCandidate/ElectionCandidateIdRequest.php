<?php

namespace App\Http\Requests\ElectionCandidate;

use Illuminate\Foundation\Http\FormRequest;

class ElectionCandidateIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidateIds' => 'required|array|min:1',
            'candidateIds.*.candidate_id' => 'required|string|exists:election_candidates,id',
            'reason' => 'nullable|string|max:2000',
        ];
    }
}
