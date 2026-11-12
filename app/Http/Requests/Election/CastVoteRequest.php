<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class CastVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_id' => 'required|string|exists:elections,id',
            // Either a full ballot ...
            'votes' => 'required_without:candidate_id|array|min:1',
            'votes.*.position_id' => 'required_with:votes|string',
            'votes.*.candidate_id' => 'required_with:votes|string',
            // ... or a single choice (legacy payload).
            'position_id' => 'required_with:candidate_id|string',
            'candidate_id' => 'required_without:votes|string',
        ];
    }

    public function ballot(): array
    {
        return $this->filled('votes')
            ? array_values($this->input('votes'))
            : [['position_id' => $this->input('position_id'), 'candidate_id' => $this->input('candidate_id')]];
    }
}
