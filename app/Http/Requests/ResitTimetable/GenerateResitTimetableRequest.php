<?php

namespace App\Http\Requests\ResitTimetable;

use App\Http\Requests\ExamTimetable\GenerateExamTimetableRequest;

/**
 * Same constraints as an exam timetable, for a resit exam: invigilators are
 * the resit exam's own.
 */
class GenerateResitTimetableRequest extends GenerateExamTimetableRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['exam_id']);

        $rules = [
            'resit_exam_id' => ['required', 'string', 'exists:resit_exams,id'],
            ...$rules,
            'version_id' => ['sometimes', 'nullable', 'string', 'exists:resit_timetable_versions,id'],
        ];

        foreach ($rules as $field => $fieldRules) {
            $rules[$field] = array_map(
                fn($rule) => $rule === 'exists:exam_invigilators,id' ? 'exists:resit_invigilators,id' : $rule,
                $fieldRules
            );
        }

        return $rules;
    }
}
