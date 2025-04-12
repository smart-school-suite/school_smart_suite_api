<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateParentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parents' => 'required|array',
            'parents.*.parent_id' => 'required|string|exists:parents,id',
            'parents.*.name' => 'sometimes|nullable|string',
            'parents.*.phone_one' => 'sometimes|nullable|string',
            'parents.*.relationship_to_student' => 'sometimes|nullable|string'
        ];
    }
}
