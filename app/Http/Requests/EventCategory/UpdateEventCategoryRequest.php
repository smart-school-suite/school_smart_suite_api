<?php

namespace App\Http\Requests\EventCategory;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RichTextMax;
use App\Http\Requests\Concerns\SanitizesRichText;

class UpdateEventCategoryRequest extends FormRequest
{
    use SanitizesRichText;

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
            'name' => 'sometimes|nullable|string|max:150',
            'description' => ['sometimes', 'nullable', 'string', new RichTextMax(5000)]
        ];
    }
}
