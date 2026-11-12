<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class PublishElectionResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tie_resolutions' => 'nullable|array',
            'tie_resolutions.*.position_id' => 'required|string',
            'tie_resolutions.*.candidate_ids' => 'required|array|min:1',
            'tie_resolutions.*.candidate_ids.*' => 'string',
        ];
    }

    public function tieResolutions(): array
    {
        return collect($this->input('tie_resolutions', []))
            ->mapWithKeys(fn($item) => [$item['position_id'] => array_values($item['candidate_ids'])])
            ->all();
    }
}
