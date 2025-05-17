<?php

namespace App\Http\Requests\StudentBatch;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentBatchRequest extends FormRequest
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
             'name' => 'sometimes|nullable|string',
             'description' => 'sometimes|nullable|string'
        ];
    }
}
