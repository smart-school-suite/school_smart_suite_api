<?php

namespace App\Http\Requests\Election\Concerns;

/**
 * Validation for the election participants configuration. Voters and
 * candidates are configured independently using the announcement audience
 * block format, plus optional exclusions.
 */
trait ValidatesElectionParticipants
{
    protected function participantRules(string $prefix = ''): array
    {
        $rules = [];
        foreach (['voters', 'candidates'] as $group) {
            $g = $prefix . $group;
            $rules += [
                $g => $prefix === '' ? 'required|array' : 'sometimes|array',
                "{$g}.school_wide" => 'nullable|boolean',

                "{$g}.admin_audience" => 'nullable|array',
                "{$g}.admin_audience.*.individual_ids" => 'nullable|array',
                "{$g}.admin_audience.*.individual_ids.*" => 'string|exists:school_admins,id',

                "{$g}.student_audience" => 'nullable|array',
                "{$g}.student_audience.*.department_ids" => 'nullable|array',
                "{$g}.student_audience.*.department_ids.*" => 'string|exists:departments,id',
                "{$g}.student_audience.*.specialty_ids" => 'nullable|array',
                "{$g}.student_audience.*.specialty_ids.*" => 'string|exists:specialties,id',
                "{$g}.student_audience.*.level_ids" => 'nullable|array',
                "{$g}.student_audience.*.level_ids.*" => 'string|exists:levels,id',
                "{$g}.student_audience.*.individual_ids" => 'nullable|array',
                "{$g}.student_audience.*.individual_ids.*" => 'string|exists:students,id',

                "{$g}.teacher_audience" => 'nullable|array',
                "{$g}.teacher_audience.*.department_ids" => 'nullable|array',
                "{$g}.teacher_audience.*.department_ids.*" => 'string|exists:departments,id',
                "{$g}.teacher_audience.*.specialty_ids" => 'nullable|array',
                "{$g}.teacher_audience.*.specialty_ids.*" => 'string|exists:specialties,id',
                "{$g}.teacher_audience.*.level_ids" => 'nullable|array',
                "{$g}.teacher_audience.*.level_ids.*" => 'string|exists:levels,id',
                "{$g}.teacher_audience.*.individual_ids" => 'nullable|array',
                "{$g}.teacher_audience.*.individual_ids.*" => 'string|exists:teachers,id',

                "{$g}.exclude" => 'nullable|array',
                "{$g}.exclude.student_ids" => 'nullable|array',
                "{$g}.exclude.student_ids.*" => 'string',
                "{$g}.exclude.teacher_ids" => 'nullable|array',
                "{$g}.exclude.teacher_ids.*" => 'string',
                "{$g}.exclude.admin_ids" => 'nullable|array',
                "{$g}.exclude.admin_ids.*" => 'string',
            ];
        }
        $rules[$prefix . 'candidates.must_be_voter'] = 'nullable|boolean';
        return $rules;
    }
}
