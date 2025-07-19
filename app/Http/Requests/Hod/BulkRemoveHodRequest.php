<?php

namespace App\Http\Requests\Hod;

use Illuminate\Foundation\Http\FormRequest;

class BulkRemoveHodRequest extends FormRequest
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
            'hods' => 'required|array',
            'hods.*.hod_id' => 'exists:hod,id'
        ];
    }
}
