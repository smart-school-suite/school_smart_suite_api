<?php

namespace App\Http\Requests\FeeInstallment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeInstallment extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|sometimes|nullable|max:100',
            'program_name' => 'string|sometimes|nullable|max:150',
            'code' => 'string|sometimes|nullable|max:5',
            'count' => 'integer|sometimes|nullable|max:10'
        ];
    }
}
