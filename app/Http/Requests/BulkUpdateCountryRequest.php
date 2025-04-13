<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateCountryRequest extends FormRequest
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
            'countries' => 'required|array',
            'countries.*.country_id' => 'required|string|exists:country,id',
            'countries.*.country' => 'sometimes|nullable|string|unique',
            'countries.*.code' => 'sometimes|nullable|string',
            'countries.*.currency' => 'sometimes|nullable|string',
            'countries.*.officail_language' => 'sometimes|nullable|string'
        ];
    }
}
