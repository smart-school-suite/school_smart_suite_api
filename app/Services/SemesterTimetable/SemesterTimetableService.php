<?php

namespace App\Services\SemesterTimetable;

use App\Constant\Constraint\SemesterTimetable\Builder\ConstraintBuilder;
use App\Models\SemesterTimetable\SemesterTimetable;
use App\Models\SemesterTimetable\SemesterTimetableError;
use App\Models\Course\SemesterJointCourse;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;

class SemesterTimetableService
{
    public function getTimetableStatus(string $versionId, object $currentSchool): ?string
    {
        $semesterTimetable = SemesterTimetable::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        return $semesterTimetable->status;
    }

    public function getTimetableSlots(string $versionId, object $currentSchool): ?array
    {
        $semesterTimetable =  SemesterTimetable::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$semesterTimetable) {
            return [];
        }

        $days = json_decode(json_encode($semesterTimetable->timetable_slots ?? []), true) ?? [];

        return $this->markJointCourses($days, $currentSchool->id, $semesterTimetable->school_semester_id);
    }

    /**
     * Flags every slot with is_joint, and gives joint slots a joint_course
     * block: which joint course it is and the other specialties sharing it.
     * Done on read so timetables generated earlier get it too.
     */
    private function markJointCourses(array $days, string $schoolBranchId, ?string $schoolSemesterId): array
    {
        $isJoint = fn(array $slot) => ($slot['slot_type'] ?? null) === GridSlotDTO::TYPE_JOINT;

        $jointCourseIds = collect($days)
            ->flatMap(fn(array $day) => $day['slots'] ?? [])
            ->filter($isJoint)
            ->pluck('course_id')
            ->filter()
            ->unique()
            ->values();

        $jointCourses = $jointCourseIds->isEmpty() || !$schoolSemesterId
            ? collect()
            : SemesterJointCourse::where('school_branch_id', $schoolBranchId)
                ->whereIn('course_id', $jointCourseIds)
                ->whereHas('semesterJointCourseRef', fn($query) => $query->where('school_semester_id', $schoolSemesterId))
                ->with('semesterJointCourseRef.schoolSemester.schoolYear.specialty.level')
                ->get()
                ->keyBy('course_id');

        return collect($days)->map(fn(array $day) => [
            ...$day,
            'slots' => collect($day['slots'] ?? [])->map(function (array $slot) use ($isJoint, $jointCourses, $schoolSemesterId) {
                if (!$isJoint($slot)) {
                    return [...$slot, 'is_joint' => false, 'joint_course' => null];
                }

                $jointCourse = $jointCourses->get($slot['course_id'] ?? null);
                $sharedWith = $jointCourse
                    ? $jointCourse->semesterJointCourseRef
                        ->filter(fn($ref) => $ref->school_semester_id !== $schoolSemesterId)
                        ->map(fn($ref) => $ref->schoolSemester?->schoolYear?->specialty)
                        ->filter()
                        ->unique('id')
                        ->map(fn($specialty) => [
                            'specialty_id' => $specialty->id,
                            'specialty_name' => $specialty->specialty_name,
                            'level_name' => $specialty->level?->name,
                            'label' => trim($specialty->specialty_name . ($specialty->level?->name ? " · {$specialty->level->name}" : '')),
                        ])
                        ->values()
                        ->all()
                    : [];

                return [
                    ...$slot,
                    'is_joint' => true,
                    'joint_course' => [
                        'semester_joint_course_id' => $jointCourse?->id,
                        'shared_with' => $sharedWith,
                        'shared_with_count' => count($sharedWith),
                    ],
                ];
            })->values()->all(),
        ])->values()->all();
    }

    public function getRequestPayload(string $versionId, object $currentSchool): ?array
    {
        $semesterTimetable =  SemesterTimetable::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        return $semesterTimetable->request_payload;
    }

    public function getParsedDiagnostics(string $versionId, object $currentSchool): ?array
    {
        $semesterTimetable = SemesterTimetable::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();

        return $semesterTimetable->parsed_diagnostics;
    }

    public function getRawDiagnostics(string $versionId, object $currentSchool): ?array
    {
        $result = [];
        $semesterTimetable = SemesterTimetable::where("timetable_version_id",  $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        $diagnostics = [
            ...$semesterTimetable->raw_diagnostics['hard'],
            ...$semesterTimetable->raw_diagnostics['soft']
        ];
        foreach ($diagnostics as $diagnostic) {
            $result[] = [
                "constraint_key" => $diagnostic["constraint_failed"]['type'],
                "constraint_id" => $diagnostic['constraint_failed']["id"],
                "constraint_title" => ConstraintBuilder::getConstraintTitle($diagnostic["constraint_failed"]['type']),
                "constraint_type" => ConstraintBuilder::getConstraintType($diagnostic["constraint_failed"]['type']),
                ...$diagnostic["constraint_failed"]["details"],
                "blocker_count" => count($diagnostic['blockers'])
            ];
        }
        return $result;
    }

    public function getErrors(string $versionId, object $currentSchool): ?array
    {
        $errors = SemesterTimetableError::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        return $errors->errors;
    }
}
