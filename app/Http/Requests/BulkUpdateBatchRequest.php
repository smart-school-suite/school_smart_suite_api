<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateBatchRequest extends FormRequest
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
          'student_batches' => 'required|array',
          'student_batches.*.batch_id' => 'required|batch_id|exists:table,column',
          'student_batches.*.name' => 'sometimes|nullable|string',
          'student_batches.*.description' => 'sometimes|nullable|string'
        ];
    }
}
