<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\SemesterTimetable\SemesterTimetableDraft;

class SemesterTimetableDraftService
{
    public function deleteTimetableDraft(string $draftId, string $semesterId, $currentSchool)
    {
        $draft = SemesterTimetableDraft::where('id', $draftId)
            ->where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $semesterId)
            ->firstOrFail();

        $semesterId = $draft->school_semester_id;

        $draft->delete();

        $remaining = SemesterTimetableDraft::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $semesterId)
            ->orderBy('draft_count', 'asc')
            ->get();

        foreach ($remaining as $index => $item) {
            $newNumber = $index + 1;
            $item->updateQuietly([
                'draft_count' => $newNumber,
                'name'        => "Draft {$newNumber}",
            ]);
        }
    }
    public function getTimetableDrafts(string $semesterId, object $currentSchool)
    {
        return SemesterTimetableDraft::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $semesterId)
            ->orderBy('draft_count', 'asc')
            ->get();
    }
    public function createTimetableDraft(array $data, object $currentSchool)
    {
        $semesterId = $data['semester_id'];

        $count = SemesterTimetableDraft::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $semesterId)
            ->count();

        if ($count >= 5) {
            throw new AppException(
                "Maximum number of timetable drafts reached for this semester.",
                400,
                "Max Number of Timetable Drafts Reached"
            );
        }

        $nextNumber = $count + 1;

        return SemesterTimetableDraft::create([
            'name'               => "Draft {$nextNumber}",
            'school_semester_id' => $semesterId,
            'school_branch_id'   => $currentSchool->id,
            'draft_count'        => $nextNumber,
        ]);
    }
    public function getTimetableDraftVersions(string $semesterId, $currentSchool)
    {
        $timetableDrafts = SemesterTimetableDraft::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $semesterId)
            ->with('timetableVersions')
            ->orderBy('draft_count', 'asc')
            ->get();

        $timetableDrafts->map(function ($draft) {
            return [
                'id'             => $draft->id,
                'name'           => $draft->name,
                'draft_count'    => $draft->draft_count,
                'versions'       => $draft->timetableVersions,
            ];
        });
    }
}
