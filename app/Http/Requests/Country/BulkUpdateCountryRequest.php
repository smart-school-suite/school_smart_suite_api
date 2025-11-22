<?php

namespace App\Http\Requests\Country;

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
            'country' => 'required|array',
            'country.*.country_id' => 'required|string|exists:countries,id',
            'country.*.country' => 'sometimes|nullable|string',
            'country.*.code' => 'sometimes|nullable|string',
            'country.*.currency' => 'sometimes|nullable|string',
            'country.*.official_language' => 'sometimes|nullable|string'
        ];
    }
}
