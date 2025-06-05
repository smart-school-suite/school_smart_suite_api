<?php

namespace App\Http\Requests\AnnouncementCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementCategoryRequest extends FormRequest
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
           'name' => 'nullable|sometimes|string|max:100',
           'description' => 'nullable|sometimes|string|max:1000'
        ];
    }
}
