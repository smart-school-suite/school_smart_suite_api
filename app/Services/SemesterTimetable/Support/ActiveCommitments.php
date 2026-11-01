<?php

namespace App\Services\SemesterTimetable\Support;

use App\Models\Course\JointCourseSlot;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Schedular\SemesterTimetable\Solution\Support\Time;
use Illuminate\Support\Collection;

class ActiveCommitments
{
    public function timetableLessons(
        string $schoolBranchId,
        ?string $exceptSemesterId = null,
        ?string $onlySemesterId = null,
        ?string $day = null,
        array $teacherIds = [],
        array $hallIds = [],
    ): Collection {
        return SemesterTimetableSlot::where('school_branch_id', $schoolBranchId)
            ->where('break', false)
            ->when($day, fn($query) => $query->where('day_of_week', strtolower($day)))
            ->when($teacherIds || $hallIds, fn($query) => $query->where(fn($resource) => $resource
                ->when($teacherIds, fn($q) => $q->orWhereIn('teacher_id', $teacherIds))
                ->when($hallIds, fn($q) => $q->orWhereIn('hall_id', $hallIds))))
            ->whereHas('semesterTimetableVersion', fn($query) => $query->active()
                ->when($exceptSemesterId, fn($q) => $q->where('school_semester_id', '!=', $exceptSemesterId))
                ->when($onlySemesterId, fn($q) => $q->where('school_semester_id', $onlySemesterId)))
            ->with(['course', 'teacher', 'hall', 'semesterTimetableVersion.schoolSemester.semester'])
            ->get()
            ->map(fn(SemesterTimetableSlot $slot) => self::normalize([
                'day' => strtolower($slot->day_of_week),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'teacher_id' => $slot->teacher_id,
                'teacher_name' => $slot->teacher?->name,
                'course_id' => $slot->course_id,
                'course_name' => $slot->course?->course_title,
                'hall_id' => $slot->hall_id,
                'hall_name' => $slot->hall?->name,
                'source' => self::sourceLabel($slot->semesterTimetableVersion),
            ], 'regular'))
            ->filter()
            ->values();
    }

    public function jointCourseSlots(
        string $schoolBranchId,
        ?string $sharedWithSemesterId = null,
        ?string $day = null,
        array $teacherIds = [],
        array $hallIds = [],
        bool $onlySharedWithSemester = false,
    ): Collection {
        return JointCourseSlot::where('school_branch_id', $schoolBranchId)
            ->when($day, fn($query) => $query->where('day', strtolower($day)))
            ->when($teacherIds || $hallIds, fn($query) => $query->where(fn($resource) => $resource
                ->when($teacherIds, fn($q) => $q->orWhereIn('teacher_id', $teacherIds))
                ->when($hallIds, fn($q) => $q->orWhereIn('hall_id', $hallIds))))
            ->whereHas('semesterJointCourse.semesterJointCourseRef.schoolSemester', fn($query) => $query->stillRunning())
            ->when($onlySharedWithSemester && $sharedWithSemesterId, fn($query) => $query
                ->whereHas('semesterJointCourse.semesterJointCourseRef', fn($q) => $q->where('school_semester_id', $sharedWithSemesterId)))
            ->with(['teacher', 'hall', 'semesterJointCourse.course', 'semesterJointCourse.semesterJointCourseRef'])
            ->get()
            ->map(fn(JointCourseSlot $slot) => self::normalize([
                'day' => strtolower($slot->day),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'teacher_id' => $slot->teacher_id,
                'teacher_name' => $slot->teacher?->name,
                'course_id' => $slot->semesterJointCourse?->course_id,
                'course_name' => $slot->semesterJointCourse?->course?->course_title,
                'hall_id' => $slot->hall_id,
                'hall_name' => $slot->hall?->name,
                'source' => 'Joint course',
                'shared_with_semester' => $sharedWithSemesterId
                    && ($slot->semesterJointCourse?->semesterJointCourseRef->contains('school_semester_id', $sharedWithSemesterId) ?? false),
            ], 'joint'))
            ->filter()
            ->values();
    }

    public static function normalize(array $slot, string $type): ?array
    {
        $start = Time::toMinutes((string) ($slot['start_time'] ?? ''));
        $end = Time::toMinutes((string) ($slot['end_time'] ?? ''));

        if ($start === null || $end === null || $end <= $start || empty($slot['day']) || $type === 'free') {
            return null;
        }

        return [
            ...$slot,
            'type' => $type,
            'start' => $start,
            'end' => $end,
            'start_time' => Time::toTime($start),
            'end_time' => Time::toTime($end),
            'teacher_id' => $slot['teacher_id'] ?? null,
            'course_id' => $slot['course_id'] ?? null,
            'hall_id' => $slot['hall_id'] ?? null,
        ];
    }

    public static function sourceLabel(?SemesterTimetableVersion $version): string
    {
        $semester = $version?->schoolSemester?->semester?->name;

        return trim(($version?->label ?? 'An active timetable') . ($semester ? " of {$semester}" : ''));
    }
}
