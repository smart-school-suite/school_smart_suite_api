<?php

namespace App\Http\Requests\ElectionOffice;

use Illuminate\Foundation\Http\FormRequest;

class TerminateOfficeHolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ended_at' => 'nullable|date_format:Y-m-d',
            'remarks' => 'nullable|string|max:2000',
        ];
    }
}
