<?php

namespace App\Http\Requests\StudentBatch;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\SanitizesRichText;

class CreateStudentBatchRequest extends FormRequest
{
    use SanitizesRichText;

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
            'name' => 'required|string',
            'description' => 'required|string'
        ];
    }
}
