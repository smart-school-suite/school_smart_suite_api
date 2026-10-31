<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class RemoveEventImagesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image_ids' => 'required|array|min:1|max:5',
            'image_ids.*' => 'uuid|exists:school_event_pics,id',
        ];
    }
}
