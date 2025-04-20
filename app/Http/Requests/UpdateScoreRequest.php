<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
    //{
      //  return false;
    //}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'scores_entries' => 'required|array',
            'scores_entries.*.mark_id' => 'required|string|exists:marks,id',
            'scores_entries.*.new_score' => [
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
            ]
        ];
    }
}
