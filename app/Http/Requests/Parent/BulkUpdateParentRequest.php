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
            'parents.*.phone_one' => 'sometimes|nullable|string',
            'parents.*.phone_two' => 'sometimes|nullable|string',
            'parents.*.email' => 'sometimes|nullable|string|email',
            'parents.*.address' => 'sometimes|nullable|string',
            'parents.*.relationship_to_student' => 'sometimes|nullable|string'
        ];
    }
}
