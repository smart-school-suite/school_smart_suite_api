<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSemesterRequest extends FormRequest
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
            'semester' => 'required|array',
            'semester.*.name' => 'sometimes|string|required',
            'semester.*.program_name' => 'sometimes|string|required',
            'semester.*.count' =>  'sometimes|required|integer'
        ];
    }
}
