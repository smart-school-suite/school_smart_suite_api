<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Election\Concerns\ValidatesElectionParticipants;
use Illuminate\Validation\Validator;

class ConfigureElectionParticipantsRequest extends FormRequest
{
    use ValidatesElectionParticipants;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->participantRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach (['voters', 'candidates'] as $group) {
                $block = $this->input($group, []);
                $configured = !empty($block['school_wide'])
                    || !empty(array_filter($block['student_audience'] ?? []))
                    || !empty(array_filter($block['teacher_audience'] ?? []))
                    || !empty(array_filter($block['admin_audience'] ?? []));
                if (!$configured) {
                    $validator->errors()->add($group, "Select at least one audience for {$group}.");
                }
            }
        });
    }
}
