<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SchoolSubscriptionRequest extends FormRequest
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
            'rates_card_id' => 'required|string|exists:rate_cards,id',
            'num_students' => 'required|integer',
            'billing_frequency' => 'required|string',
            'school_name' => 'required|string|max:250',
            'country_id' => 'required|string|exists:countries,id',
            'type' => 'required|string|in:private,government',
            'school_branch_name' => 'required|string|max:300',
            'abbreviation' => 'required|string|max:20'
        ];
    }
}
