<?php

namespace App\Services\ResitTimetable\Support;

use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * A course resat by several specialties is sat once, at the same time, so its
 * paper is not seen before every candidate has written it. Once one
 * specialty's resit timetable that examines the course is active, the others
 * (same academic year and resit exam type) place it at that time.
 *
 * Only times inside this resit exam's period are used; a course examined
 * outside it elsewhere is placed freely.
 */
class JointResitCourses
{
    /** @return array[] required_joint_course_periods: [{course_id, date, start_time, end_time}] */
    public function periodsFor(string $schoolBranchId, ResitExam $resitExam, array $courseIds, string $startDate, string $endDate): array
    {
        return $this->sessions($schoolBranchId, $resitExam, $courseIds)
            ->filter(fn(array $session) => $session['date'] >= $startDate && $session['date'] <= $endDate)
            ->unique('course_id')
            ->map(fn(array $session) => [
                'course_id' => $session['course_id'],
                'date' => $session['date'],
                'start_time' => $session['start_time'],
                'end_time' => $session['end_time'],
            ])
            ->values()
            ->all();
    }

    /** @return Collection<array> [{course_id, date, start_time, end_time, resit_exam_id, specialty_name}] earliest first */
    public function sessions(string $schoolBranchId, ResitExam $resitExam, array $courseIds): Collection
    {
        if (empty($courseIds)) {
            return collect();
        }

        $resitExam->loadMissing('schoolYear');
        $academicYearId = $resitExam->schoolYear?->system_academic_year_id;

        $siblingIds = ResitExam::where('school_branch_id', $schoolBranchId)
            ->where('id', '!=', $resitExam->id)
            ->where('exam_type_id', $resitExam->exam_type_id)
            ->when($academicYearId, fn($query) => $query->whereHas(
                'schoolYear',
                fn($year) => $year->where('system_academic_year_id', $academicYearId)
            ))
            ->pluck('id');

        if ($siblingIds->isEmpty()) {
            return collect();
        }

        return Resitexamtimetable::where('school_branch_id', $schoolBranchId)
            ->whereIn('resit_exam_id', $siblingIds)
            ->whereIn('course_id', $courseIds)
            ->whereNotNull('version_id')
            ->with('resitExam.schoolYear.specialty')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn(Resitexamtimetable $slot) => [
                'course_id' => $slot->course_id,
                'date' => Carbon::parse($slot->getRawOriginal('date'))->format('Y-m-d'),
                'start_time' => Time::normalize((string) $slot->getRawOriginal('start_time')),
                'end_time' => Time::normalize((string) $slot->getRawOriginal('end_time')),
                'resit_exam_id' => $slot->resit_exam_id,
                'specialty_name' => $slot->resitExam?->schoolYear?->specialty?->specialty_name,
            ])
            ->values();
    }
}
