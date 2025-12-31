<?php

namespace App\Http\Requests\StudentSource;

use Illuminate\Foundation\Http\FormRequest;

class CreateStudentSourceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => "required|string|max:150",
            "description" => "required|string|max:500"
        ];
    }
}
