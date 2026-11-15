<?php

namespace App\Services\ResitTimetable\Support;

use App\Models\ResitExam;
use App\Models\ResitJointCourse\ResitJointCourse;
use App\Models\ResitJointCourse\ResitJointCourseSlot;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Services\ResitJointCourse\ResitJointCourseSync;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * The joint resits of a resit exam, as its timetable needs them. A course
 * students of several specialties resit is sat once, at the time set on the
 * resit joint course timetable (resit_jc_slots); every linked resit timetable
 * places it exactly there, and generation waits until each one has a time.
 */
class JointResitCourses
{
    public function __construct(private ResitJointCourseSync $sync) {}

    /** Links the resit exam to its joint resits as they are now. */
    public function sync(string $schoolBranchId, string $resitExamId): void
    {
        $this->sync->forResitExam($schoolBranchId, $resitExamId);
    }

    /** @return Collection<ResitJointCourse> the resit exam's joint resits, with their course and time */
    public function of(string $schoolBranchId, string $resitExamId): Collection
    {
        return ResitJointCourse::where('school_branch_id', $schoolBranchId)
            ->whereHas('refs', fn($query) => $query->where('resit_exam_id', $resitExamId))
            ->with(['course', 'slots', 'refs.resitExam.schoolYear.specialty'])
            ->get();
    }

    /** @return array[] required_joint_course_periods: [{course_id, date, start_time, end_time}] */
    public function periodsFor(string $schoolBranchId, string $resitExamId): array
    {
        return $this->of($schoolBranchId, $resitExamId)
            ->map(fn(ResitJointCourse $course) => [$course, $this->slotOf($course)])
            ->filter(fn(array $pair) => $pair[1] !== null)
            ->map(fn(array $pair) => ['course_id' => $pair[0]->course_id, ...$pair[1]])
            ->values()
            ->all();
    }

    /** @return array[] [{resit_joint_course_id, course_id, course_title}] joint resits with no time yet */
    public function withoutTime(string $schoolBranchId, string $resitExamId): array
    {
        return $this->of($schoolBranchId, $resitExamId)
            ->filter(fn(ResitJointCourse $course) => $course->slots->isEmpty())
            ->map(fn(ResitJointCourse $course) => [
                'resit_joint_course_id' => $course->id,
                'course_id' => $course->course_id,
                'course_title' => $course->course?->course_title,
            ])
            ->values()
            ->all();
    }

    /** @return array<string, array> course_id => {resit_joint_course_id, specialties, slot|null} for the scheduling data */
    public function byCourse(string $schoolBranchId, string $resitExamId): array
    {
        return $this->of($schoolBranchId, $resitExamId)
            ->mapWithKeys(fn(ResitJointCourse $course) => [$course->course_id => [
                'resit_joint_course_id' => $course->id,
                'specialties' => $course->refs
                    ->map(fn($ref) => $ref->resitExam?->schoolYear?->specialty?->specialty_name)
                    ->filter()
                    ->values()
                    ->all(),
                'slot' => $this->slotOf($course),
            ]])
            ->all();
    }

    private function slotOf(ResitJointCourse $course): ?array
    {
        /** @var ?ResitJointCourseSlot $slot */
        $slot = $course->slots->sortByDesc('updated_at')->first();

        return $slot ? [
            'date' => Carbon::parse($slot->getRawOriginal('date'))->format('Y-m-d'),
            'start_time' => Time::normalize((string) $slot->getRawOriginal('start_time')),
            'end_time' => Time::normalize((string) $slot->getRawOriginal('end_time')),
        ] : null;
    }
}
