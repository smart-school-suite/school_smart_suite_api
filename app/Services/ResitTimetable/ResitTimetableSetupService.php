<?php

namespace App\Services\ResitTimetable;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Exceptions\AppException;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\ResitExam;
use App\Models\ResitTimetable\ResitInvigilator;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Services\ExamTimetable\ExamTimetableSetupService;
use App\Services\ResitTimetable\Support\JointResitCourses;
use App\Services\ResitTimetable\Support\ResitCandidates;
use Carbon\Carbon;

/**
 * What the resit timetable page needs before generating: the constraints (the
 * exam timetable's) and the resit exam's courses with their candidates, halls
 * and invigilators.
 */
class ResitTimetableSetupService
{
    public function __construct(
        private ExamTimetableSetupService $examSetup,
        private ResitCandidates $candidates,
        private JointResitCourses $jointCourses,
    ) {}

    public function constraints(): array
    {
        return collect($this->examSetup->constraints())
            ->map(fn(array $category) => [
                ...$category,
                'constraints' => array_values(array_map(
                    fn(array $item) => $item['key'] === RequiredJointCourseSession::KEY
                        ? [...$item, 'name' => 'Shared Resit Courses', 'description' => 'A course resat by several specialties is sat once: once another specialty\'s active resit timetable examines it, it is placed at that same time here.', 'read_only' => true]
                        : $item,
                    $category['constraints']
                )),
            ])
            ->values()
            ->all();
    }

    public function schedulingData(object $currentSchool, string $resitExamId): array
    {
        $resitExam = $this->resitExam($currentSchool, $resitExamId);
        $byCourse = $this->candidates->byCourse($currentSchool->id, $resitExam);

        $courses = Courses::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', array_keys($byCourse))
            ->orderBy('course_title')
            ->get();

        $joint = $this->jointCourses->sessions($currentSchool->id, $resitExam, $courses->pluck('id')->all())
            ->unique('course_id')
            ->keyBy('course_id');

        $invigilators = ResitInvigilator::where('school_branch_id', $currentSchool->id)
            ->where('resit_exam_id', $resitExam->id)
            ->with('invigilatable')
            ->get();

        $taught = TeacherCoursePreference::where('school_branch_id', $currentSchool->id)
            ->whereIn('teacher_id', $invigilators->where('actorable_type', Teacher::class)->pluck('actorable_id'))
            ->get(['teacher_id', 'course_id'])
            ->groupBy('teacher_id');

        return [
            'resit_exam' => $this->summary($resitExam, collect($byCourse)->flatten()->unique()->count()),
            // Kept as "exam" too, so the shared constraint editors read the period the same way.
            'exam' => $this->summary($resitExam, collect($byCourse)->flatten()->unique()->count()),
            'courses' => $courses->map(fn($course) => [
                'course_id' => $course->id,
                'course_title' => $course->course_title,
                'course_code' => $course->course_code,
                'candidate_count' => count($byCourse[$course->id] ?? []),
                'is_joint' => $joint->has($course->id),
                'joint' => $joint->has($course->id) ? [
                    'resit_exam_id' => $joint[$course->id]['resit_exam_id'],
                    'specialty_name' => $joint[$course->id]['specialty_name'],
                    'slot' => [
                        'date' => $joint[$course->id]['date'],
                        'start_time' => $joint[$course->id]['start_time'],
                        'end_time' => $joint[$course->id]['end_time'],
                    ],
                ] : null,
            ])->values()->all(),
            'halls' => Hall::where('school_branch_id', $currentSchool->id)
                ->where('status', 'available')
                ->whereHas('types', fn($query) => $query->where('key', 'exam.hall'))
                ->orderBy('name')
                ->get()
                ->map(fn(Hall $hall) => ['hall_id' => $hall->id, 'hall_name' => $hall->name, 'capacity' => (int) $hall->capacity])
                ->values()
                ->all(),
            'invigilators' => $invigilators->map(fn(ResitInvigilator $invigilator) => [
                'invigilator_id' => $invigilator->id,
                'name' => $invigilator->invigilatable?->name,
                'type' => $invigilator->actorable_type === Teacher::class ? 'teacher' : 'school_admin',
                'courses_taught' => $invigilator->actorable_type === Teacher::class
                    ? ($taught[$invigilator->actorable_id] ?? collect())->pluck('course_id')->unique()->values()->all()
                    : [],
            ])->values()->all(),
        ];
    }

    public function resitExam(object $currentSchool, string $resitExamId): ResitExam
    {
        return ResitExam::where('school_branch_id', $currentSchool->id)
            ->with(['examType', 'schoolYear.specialty.level'])
            ->find($resitExamId)
            ?? throw new AppException("Resit exam {$resitExamId} not found.", 404, 'Resit Exam Not Found', "We couldn't find this resit exam. It may have been deleted.", null);
    }

    private function summary(ResitExam $resitExam, int $candidates): array
    {
        return [
            'id' => $resitExam->id,
            'exam_name' => $resitExam->examType?->exam_name,
            'start_date' => $resitExam->start_date ? Carbon::parse($resitExam->start_date)->format('Y-m-d') : null,
            'end_date' => $resitExam->end_date ? Carbon::parse($resitExam->end_date)->format('Y-m-d') : null,
            'specialty_name' => $resitExam->schoolYear?->specialty?->specialty_name,
            'level_name' => $resitExam->schoolYear?->specialty?->level?->name,
            'candidate_count' => $candidates,
        ];
    }
}
