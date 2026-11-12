<?php

namespace App\Http\Requests\ElectionApplication;

use Illuminate\Foundation\Http\FormRequest;

class ReviewElectionApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remarks' => 'nullable|string|max:2000',
        ];
    }
}
