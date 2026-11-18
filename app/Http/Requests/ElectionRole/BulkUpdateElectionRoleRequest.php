<?php

namespace App\Http\Requests\ElectionRole;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RichTextMax;
use App\Http\Requests\Concerns\SanitizesRichText;

class BulkUpdateElectionRoleRequest extends FormRequest
{
    use SanitizesRichText;

    protected function richTextFields(): array
    {
        return ['election_roles.*.description'];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'election_roles' => 'required|array|min:1',
            'election_roles.*.election_role_id' => 'required|string|exists:election_roles,id',
            'election_roles.*.name' => 'sometimes|nullable|string|max:150',
            'election_roles.*.election_type_id' => 'sometimes|nullable|string|exists:election_types,id',
            'election_roles.*.description' => ['sometimes', 'nullable', 'string', new RichTextMax(2000)],
            'election_roles.*.seats' => 'sometimes|nullable|integer|min:1|max:50',
        ];
    }
}
