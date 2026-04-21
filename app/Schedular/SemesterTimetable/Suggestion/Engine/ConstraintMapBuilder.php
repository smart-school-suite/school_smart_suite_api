<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

class ConstraintMapBuilder
{
    public function build(array $diagnostics): array
    {
        $map = [];

        foreach ($diagnostics as $diag) {

            $constraint = $diag->constraint_failed;

            $map[$constraint['id']] = [
                'id' => $constraint['id'],
                'type' => $constraint['type'],
                'details' => $constraint['details'],
                'blockers' => $diag->blockers
            ];
        }

        return $map;
    }
}
