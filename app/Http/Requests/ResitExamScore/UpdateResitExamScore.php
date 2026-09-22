<?php

namespace App\Http\Requests\ResitExamScore;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResitExamScore extends FormRequest
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
            'candidate_id' => "required|uuid|exists:resit_candidates,id",
            'scores' => 'required|array|min:1',
            'scores.*.score_id' => 'required|string|exists:resit_marks,id',
            'scores.*.score' => [
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }
}
