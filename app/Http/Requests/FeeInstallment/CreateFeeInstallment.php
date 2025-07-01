<?php

namespace App\Http\Requests\FeeInstallment;

use Illuminate\Foundation\Http\FormRequest;

class CreateFeeInstallment extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
            'name' => 'string|required|max:100',
            'program_name' => 'string|required|max:150',
            'code' => 'string|required|max:5',
            'count' => 'integer|max:10'
        ];
    }
}
