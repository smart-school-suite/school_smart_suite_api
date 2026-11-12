<?php

namespace App\Http\Requests\ElectionApplication;

use Illuminate\Foundation\Http\FormRequest;

class ElectionApplicationIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'electionApplicationIds' => 'required|array|min:1',
            'electionApplicationIds.*.election_application_id' => 'required|string|exists:election_applications,id',
            'remarks' => 'nullable|string|max:2000',
        ];
    }
}
