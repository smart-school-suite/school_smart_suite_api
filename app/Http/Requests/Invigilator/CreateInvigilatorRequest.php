<?php

namespace App\Http\Requests\Invigilator;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvigilatorRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "invigilators" => ["required", "array", "min:1"],
            "invigilators.*.actor_id" => ["required", "uuid"],
            "invigilators.*.actor_type" => ["required", "string", "in:teacher,school_admin"]
        ];
    }
}
