<?php

namespace App\Services\Course;

use App\Models\Courses;
use App\Models\Semester;
use App\Models\Specialty;
use App\Services\Semester\SemesterLimit;
use Illuminate\Support\Collection;

class CourseCreationOptions
{
    public function __construct(private SemesterLimit $limit) {}

    public function describe(object $currentSchool): array
    {
        $semesters = Semester::query()
            ->where('count', '<=', $this->limit->forBranch($currentSchool->id))
            ->orderBy('count')
            ->get(['id', 'name', 'count']);

        $courses = Courses::query()
            ->where('school_branch_id', $currentSchool->id)
            ->with('specialties:id')
            ->orderBy('course_code')
            ->get(['id', 'course_code', 'course_title', 'credit', 'semester_id', 'status']);

        $specialties = Specialty::query()
            ->where('school_branch_id', $currentSchool->id)
            ->with(['level', 'department'])
            ->get()
            ->sortBy(fn (Specialty $specialty) => [$specialty->specialty_name, (int) $specialty->level?->level])
            ->values();

        return [
            'semesters' => $semesters->map(fn (Semester $semester) => [
                'id' => $semester->id,
                'name' => $semester->name,
                'count' => (int) $semester->count,
            ])->values()->all(),
            'codes' => $courses->pluck('course_code')->map(fn ($code) => strtoupper(trim($code)))->unique()->values()->all(),
            'specialties' => $specialties
                ->map(fn (Specialty $specialty) => $this->describeSpecialty($specialty, $semesters, $courses))
                ->values()
                ->all(),
        ];
    }

    private function describeSpecialty(Specialty $specialty, Collection $semesters, Collection $courses): array
    {
        $own = $courses->filter(fn (Courses $course) => $course->specialties->contains('id', $specialty->id));

        return [
            'id' => $specialty->id,
            'label' => collect([$specialty->specialty_name, $specialty->level?->name])->filter()->implode(' · '),
            'specialty_name' => $specialty->specialty_name,
            'level_name' => $specialty->level?->name,
            'department_name' => $specialty->department?->department_name,
            'courses_count' => $own->count(),
            'semesters' => $semesters->map(function (Semester $semester) use ($own) {
                $list = $own->where('semester_id', $semester->id)->values();

                return [
                    'semester_id' => $semester->id,
                    'courses_count' => $list->count(),
                    'credits' => (int) $list->sum('credit'),
                    'courses' => $list->map(fn (Courses $course) => [
                        'id' => $course->id,
                        'course_code' => $course->course_code,
                        'course_title' => $course->course_title,
                        'credit' => (int) $course->credit,
                    ])->all(),
                ];
            })->values()->all(),
        ];
    }
}
