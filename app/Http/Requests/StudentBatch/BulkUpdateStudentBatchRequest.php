<?php

namespace App\Http\Requests\StudentBatch;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStudentBatchRequest extends FormRequest
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
            'student_batches' => 'required|array',
            'student_batches.*.student_batch_id' => 'required|string|exists:student_batch,id',
            'student_batches.*.name' => 'sometimes|nullable|string',
            'student_batches.*.description' => 'sometimes|nullable|string'
        ];
    }
}
