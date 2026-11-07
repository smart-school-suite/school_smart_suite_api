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
        array $exceptSemesterIds = [],
    ): Collection {
        return SemesterTimetableSlot::where('school_branch_id', $schoolBranchId)
            ->where('break', false)
            ->when($day, fn($query) => $query->where('day_of_week', strtolower($day)))
            ->when($teacherIds || $hallIds, fn($query) => $query->where(fn($resource) => $resource
                ->when($teacherIds, fn($q) => $q->orWhereIn('teacher_id', $teacherIds))
                ->when($hallIds, fn($q) => $q->orWhereIn('hall_id', $hallIds))))
            ->whereHas('semesterTimetableVersion', fn($query) => $query->active()
                ->when($exceptSemesterId, fn($q) => $q->where('school_semester_id', '!=', $exceptSemesterId))
                ->when($exceptSemesterIds, fn($q) => $q->whereNotIn('school_semester_id', $exceptSemesterIds))
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
                'school_semester_id' => $slot->semesterTimetableVersion?->school_semester_id,
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

    /**
     * Teacher and hall busy periods for generating a semester's timetable, in
     * the shape the scheduler reads (teacher_busy_periods / hall_busy_periods).
     *
     * Busy = lessons in the active timetables of other running semesters, plus
     * joint course slots this semester does not share. The semester's own
     * active timetable is left out because it is the one being regenerated,
     * and occurrences of its required joint periods are left out because the
     * grid already holds them (counting them again would double teacher hours).
     */
    public function busyPeriodsForGeneration(
        string $schoolBranchId,
        string $schoolSemesterId,
        array $teacherIds,
        array $hallIds,
        array $requiredJointPeriods = [],
    ): array {
        $teacherIds = array_values(array_filter($teacherIds));
        $hallIds = array_values(array_filter($hallIds));

        if (!$teacherIds && !$hallIds) {
            return ['teacher_busy_periods' => [], 'hall_busy_periods' => []];
        }

        $required = collect($requiredJointPeriods)
            ->map(fn(array $period) => self::normalize($period, 'joint'))
            ->filter();

        $isRequiredOccurrence = fn(array $slot) => $required->contains(
            fn(array $period) => $period['course_id'] === $slot['course_id']
                && $period['day'] === $slot['day']
                && $period['start'] === $slot['start']
                && $period['end'] === $slot['end']
        );

        $busy = $this->timetableLessons($schoolBranchId, exceptSemesterId: $schoolSemesterId, teacherIds: $teacherIds, hallIds: $hallIds)
            ->concat(
                $this->jointCourseSlots($schoolBranchId, sharedWithSemesterId: $schoolSemesterId, teacherIds: $teacherIds, hallIds: $hallIds)
                    ->reject(fn(array $slot) => $slot['shared_with_semester'])
            )
            ->reject($isRequiredOccurrence);

        $period = fn(array $slot) => [
            'day' => $slot['day'],
            'start_time' => $slot['start_time'],
            'end_time' => $slot['end_time'],
        ];

        return [
            'teacher_busy_periods' => $busy
                ->filter(fn(array $slot) => in_array($slot['teacher_id'], $teacherIds, true))
                ->map(fn(array $slot) => [...$period($slot), 'teacher_id' => $slot['teacher_id'], 'teacher_name' => $slot['teacher_name'] ?? null])
                ->unique(fn(array $slot) => implode('|', [$slot['teacher_id'], $slot['day'], $slot['start_time'], $slot['end_time']]))
                ->values()
                ->all(),
            'hall_busy_periods' => $busy
                ->filter(fn(array $slot) => in_array($slot['hall_id'], $hallIds, true))
                ->map(fn(array $slot) => [...$period($slot), 'hall_id' => $slot['hall_id'], 'hall_name' => $slot['hall_name'] ?? null])
                ->unique(fn(array $slot) => implode('|', [$slot['hall_id'], $slot['day'], $slot['start_time'], $slot['end_time']]))
                ->values()
                ->all(),
        ];
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
