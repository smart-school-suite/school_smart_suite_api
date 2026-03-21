<?php

namespace App\Services\SemesterTimetable;

use App\Models\SemesterTimetable\SemesterTimetableDiagnostic;

class SemesterTimetableService
{
   public function getTimetableParsedDiagnostics(string $timetableVersionId): array
    {
        $diagnostic = SemesterTimetableDiagnostic::forVersion($timetableVersionId)->latest('generated_at')->first();

        if (!$diagnostic) {
            return [];
        }

        $parsedDiagnostics = [
            'status' => $diagnostic->status,
            'summary' => $diagnostic->summary ?? null,
            'violations' => $diagnostic->violations ?? null,
            'constraint_modification_suggestions' => $diagnostic->constraint_modification_suggestions ?? null,
            'blocker_resolution_suggestions' => $diagnostic->blocker_resolution_suggestions ?? null,
            'meta' => $diagnostic->meta ?? null,
            'generated_at' => $diagnostic->generated_at
        ];
        return $parsedDiagnostics;
    }
}
