<?php

namespace App\Services\SemesterTimetable;

use App\Models\SemesterTimetable\SemesterTimetableDiagnostic;

class SemesterTimetableService
{
    public function getTimetableParsedDiagnostics(string $timetableVersionId): array
    {
        $diagnostic = SemesterTimetableDiagnostic::forVersion($timetableVersionId)
            ->latest('generated_at')
            ->first();

        if (!$diagnostic) {
            return [];
        }

        $summary = $diagnostic->summary ?? [];
        $violations = $diagnostic->violations ?? [];
        $constraintSuggestions = $diagnostic->constraint_modification_suggestions ?? [];
        $blockerSuggestions = $diagnostic->blocker_resolution_suggestions ?? [];

        $constraintSuggestionsByConstraintId = [];
        foreach ($constraintSuggestions as $s) {
            $cid = $s['constraint_id'] ?? null;
            if ($cid === null) continue;
            $constraintSuggestionsByConstraintId[$cid][] = $s;
        }

        $blockerSuggestionsByPair = [];
        foreach ($blockerSuggestions as $s) {
            $cid = $s['constraint_id'] ?? null;
            $vid = $s['violation_id'] ?? null;
            if ($cid === null || $vid === null) continue;

            $key = $cid . '|' . $vid;
            $blockerSuggestionsByPair[$key][] = $s;
        }

        $summaryGrouped = [];
        foreach ($summary as $item) {
            $cid = $item['constraint_id'] ?? null;
            $groupKey = $cid ?? '__unknown__';

            if (!isset($summaryGrouped[$groupKey])) {
                $summaryGrouped[$groupKey] = [
                    'constraint_id' => $cid,
                    'constraint_key' => $item['constraint_key'] ?? null,
                    'constraint_name' => $item['constraint_name'] ?? null,
                    'constraint_type' => $item['constraint_type'] ?? null,
                    'constraint_failed' => [],
                    'suggestions' => [],
                ];
            }

            $summaryGrouped[$groupKey]['constraint_failed'][] = $item;
        }
        foreach ($summaryGrouped as $groupKey => $group) {
            $cid = $group['constraint_id'];
            if ($cid !== null && isset($constraintSuggestionsByConstraintId[$cid])) {
                $summaryGrouped[$groupKey]['suggestions'] = $constraintSuggestionsByConstraintId[$cid];
            }
        }

        $summaryGrouped = array_values($summaryGrouped);

        $violationsWithSuggestions = [];
        foreach ($violations as $v) {
            $cid = $v['constraint_id'] ?? null;
            $vid = $v['violation_id'] ?? null;

            $key = ($cid !== null && $vid !== null) ? ($cid . '|' . $vid) : null;

            $v['suggestions'] = ($key !== null && isset($blockerSuggestionsByPair[$key]))
                ? $blockerSuggestionsByPair[$key]
                : [];

            $violationsWithSuggestions[] = $v;
        }

        return [
            'status' => $diagnostic->status,
            'summary' => $summaryGrouped,
            'violations' => $violationsWithSuggestions,
            'meta' => $diagnostic->meta ?? null,
            'generated_at' => $diagnostic->generated_at,
        ];
    }
}
