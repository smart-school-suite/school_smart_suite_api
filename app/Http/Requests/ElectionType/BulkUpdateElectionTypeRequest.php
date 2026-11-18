<?php

namespace App\Http\Requests\ElectionType;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RichTextMax;
use App\Http\Requests\Concerns\SanitizesRichText;

class BulkUpdateElectionTypeRequest extends FormRequest
{
    use SanitizesRichText;

    protected function richTextFields(): array
    {
        return ['election_types.*.description'];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_types' => 'required|array|min:1',
            'election_types.*.election_type_id' => 'required|string|exists:election_types,id',
            'election_types.*.election_title' => 'sometimes|nullable|string|max:150',
            'election_types.*.description' => ['sometimes', 'nullable', 'string', new RichTextMax(2000)],
            'election_types.*.status' => 'sometimes|nullable|in:active,inactive',
        ];
    }
}
