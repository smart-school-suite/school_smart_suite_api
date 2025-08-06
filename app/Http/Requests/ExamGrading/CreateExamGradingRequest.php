<?php

namespace App\Http\Requests\ExamGrading;

use Illuminate\Foundation\Http\FormRequest;

class CreateExamGradingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Define your validation rules here
            // 'field_name' => 'required|string|max:255',
        ];
    }
}
