<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateParentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'parents' => 'required|array',
            'parents.*.parent_id' => 'required|string|exists:parents,id',
            'parents.*.name' => 'sometimes|nullable|string',
            'parents.*.phone' => 'sometimes|nullable|string',
            'parents.*.address' => 'sometimes|nullable|string',
            'parents.*.preferred_contact_method' => 'sometimes|nullable|string',
            'parents.*.preferred_language' => 'sometimes|nullable|string'
        ];
    }
}
