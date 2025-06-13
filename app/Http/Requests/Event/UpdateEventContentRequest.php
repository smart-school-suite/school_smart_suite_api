<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateEventContentRequest extends FormRequest
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
            'title' => 'sometimes|nullable|string|max:200',
            'description' => 'sometimes|nullable|string|max:5000',
            'background_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:2048',
            'organizer' => 'sometimes|nullable|string|max:255',
            'location' => 'sometimes|nullable|string|max:255',
            'start_date' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'end_date' => 'sometimes|nullable|date_format:Y-m-d H:i|after_or_equal:start_date',
            'event_category_id' => 'sometimes|nullable|exists:event_categories,id',
            'tag_id' => 'sometimes|nullable|exists:event_tags,id',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $inputFields = [
                'title',
                'description',
                'background_image',
                'organizer',
                'location',
                'start_date',
                'end_date',
                'event_category_id',
                'tag_id',
            ];

            $atLeastOneFieldPresent = false;
            foreach ($inputFields as $field) {
                if ($field === 'background_image') {
                    if ($this->hasFile($field)) {
                        $atLeastOneFieldPresent = true;
                        break;
                    }
                } else {
                    if ($this->filled($field)) {
                        $atLeastOneFieldPresent = true;
                        break;
                    }
                }
            }

            if (!$atLeastOneFieldPresent) {
                $validator->errors()->add('general', 'At least one field (title, description, background image, organizer, location, start date, end date, category, or tag) must be provided.');
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Custom messages for specific fields if needed
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'event_category_id.exists' => 'The selected event category does not exist.',
            'tag_id.exists' => 'The selected tag does not exist.',
            'background_image.max' => 'The background image may not be greater than 2MB.',
        ];
    }
}
