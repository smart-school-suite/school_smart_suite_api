<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateEventDraftRequest extends FormRequest
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
        $rules = [
            'status' => ['required', 'string', Rule::in(['active', 'scheduled'])],
            'published_at' => 'nullable|date_format:Y-m-d H:i',
        ];

                switch ($this->input('status')) {
            case 'active':
                $rules = array_merge($rules, [
                    'published_at' => 'nullable',
                ]);
                break;

            case 'scheduled':
                $rules = array_merge($rules, [
                    'published_at' => 'required|date_format:Y-m-d H:i|after:now'
                ]);
                break;
        }
         return $rules;

    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $todaysDate = now();
        return [
            'published_at.after_or_equal' => "The :attribute must be today's date:  $todaysDate or a future date.",
            'published_at.after' => 'The :attribute must be a future date and time for scheduled events.',
            'date_format' => 'The :attribute is not a valid date format. Please use YYYY-MM-DD HH:MM.',
        ];
    }
}
