<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementContentRequest extends FormRequest
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
            'title' => 'sometimes|nullable|string|max:150',
            "content" => 'sometimes|nullable|string|max:1000',
            'category_id' => ['sometimes', 'nullable', 'exists:announcement_categories,id'],
            'label_id' => ['sometimes','nullable', 'string', 'exists:labels,id'],
            'tag_ids' => 'nullable|sometimes|array',
            'tag_ids.*.tag_id' => 'nullable|sometimes|string|exists:tags,id',
        ];
    }
}
