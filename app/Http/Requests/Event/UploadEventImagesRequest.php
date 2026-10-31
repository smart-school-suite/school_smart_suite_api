<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UploadEventImagesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'images' => 'Event Images',
            'images.*' => 'Event Image',
        ];
    }

    public function messages(): array
    {
        return [
            'images.max' => 'An event can have at most 5 images.',
            'images.*.max' => 'Each event image must be 2 MB or smaller.',
        ];
    }
}
