<?php

namespace App\Http\Requests\ElectionType;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RichTextMax;
use App\Http\Requests\Concerns\SanitizesRichText;

class UpdateElectionTypeRequest extends FormRequest
{
    use SanitizesRichText;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_title' => 'sometimes|nullable|string|max:150',
            'description' => ['sometimes', 'nullable', 'string', new RichTextMax(2000)],
        ];
    }
}
