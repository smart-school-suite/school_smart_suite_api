<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateCountryRequest extends FormRequest
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
            'countrys' => 'required|array',
            'countrys.*.country_id' => 'required|string|exists:country,id',
            'countrys.*.country' => 'sometimes|nullable|string|unique',
            'countrys.*.code' => 'sometimes|nullable|string',
            'countrys.*.currency' => 'sometimes|nullable|string',
            'countrys.*.official_language' => 'sometimes|nullable|string'
        ];
    }
}
