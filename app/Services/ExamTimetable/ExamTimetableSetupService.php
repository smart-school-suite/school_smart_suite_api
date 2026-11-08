<?php

namespace App\Services\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use App\Exceptions\AppException;
use App\Models\AccessedStudent;
use App\Models\Course\CourseSpecialty;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\Exams;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\Hall;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use Carbon\Carbon;

/**
 * What the exam timetable page needs before generating: the constraints it
 * can configure, and the exam's courses, halls and invigilators to configure
 * them with.
 */
class ExamTimetableSetupService
{
    public const CANDIDATES_PER_INVIGILATOR = 'candidates_per_invigilator';

    /** Constraints grouped by category, in the shape of the semester constraint list. */
    public function constraints(): array
    {
        $categories = [
            'schedule_constraint' => ['Schedule', [OperationalPeriod::class, SessionDuration::class, StudentDailyLoadRange::class]],
            'course_constraint' => ['Courses', [CourseTimeRequest::class, RequiredJointCourseSession::class]],
            'hall_constraint' => ['Halls', [HallRequestedSlot::class]],
            'invigilator_constraint' => ['Invigilators', [InvigilatorRequestedSlot::class]],
            'assignment_constraint' => ['Assignments', [RequestedAssignment::class]],
        ];

        $list = [];
        foreach ($categories as $key => [$name, $constraints]) {
            $items = array_map(fn(string $class) => [
                'id' => $class::KEY,
                'key' => $class::KEY,
                'name' => $class::TITLE,
                'description' => $class::DESCRIPTION,
                'type' => $class::TYPE,
            ], $constraints);

            if ($key === 'invigilator_constraint') {
                $items[] = [
                    'id' => self::CANDIDATES_PER_INVIGILATOR,
                    'key' => self::CANDIDATES_PER_INVIGILATOR,
                    'name' => 'Candidates Per Invigilator',
                    'description' => 'How many candidates one invigilator supervises. Each hall gets at least one invigilator; with this set, bigger groups get more.',
                    'type' => 'hard',
                ];
            }

            $list[] = ['id' => $key, 'key' => $key, 'category' => $name, 'constraints' => $items];
        }

        return $list;
    }

    /** The exam's period, courses, exam halls and invigilators, for configuring constraints. */
    public function schedulingData(object $currentSchool, string $examId): array
    {
        $exam = Exams::where('school_branch_id', $currentSchool->id)
            ->with(['examtype', 'schoolYear.specialty.level'])
            ->find($examId)
            ?? throw new AppException("Exam {$examId} not found.", 404, 'Exam Not Found', "We couldn't find this exam. It may have been deleted.", null);

        $specialtyId = $exam->schoolYear?->specialty_id;
        $semesterId = $exam->examtype?->semester_id;

        $courses = $specialtyId
            ? CourseSpecialty::where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $specialtyId)
                ->whereHas('course', fn($query) => $query->when($semesterId, fn($q) => $q->where('semester_id', $semesterId)))
                ->with('course')
                ->get()
                ->pluck('course')
                ->filter(fn($course) => $course && ($course->status ?? 'active') === 'active')
                ->unique('id')
                ->values()
            : collect();

        $jointSlots = ExamJointCourseRef::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam->id)
            ->with('examJointCourse.examJointCourseSlot')
            ->get()
            ->mapWithKeys(function (ExamJointCourseRef $ref) {
                $slot = $ref->examJointCourse?->examJointCourseSlot->first();
                return [$ref->examJointCourse?->course_id => [
                    'exam_joint_course_id' => $ref->exam_js_id,
                    'slot' => $slot ? [
                        'date' => Carbon::parse($slot->date)->format('Y-m-d'),
                        'start_time' => Time::normalize((string) $slot->getRawOriginal('start_time')),
                        'end_time' => Time::normalize((string) $slot->getRawOriginal('end_time')),
                    ] : null,
                ]];
            });

        $invigilators = ExamInvigilator::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam->id)
            ->with('invigilatable')
            ->get();

        $taught = TeacherCoursePreference::where('school_branch_id', $currentSchool->id)
            ->whereIn('teacher_id', $invigilators->where('actorable_type', Teacher::class)->pluck('actorable_id'))
            ->get(['teacher_id', 'course_id'])
            ->groupBy('teacher_id');

        return [
            'exam' => [
                'id' => $exam->id,
                'exam_name' => $exam->examtype?->exam_name,
                'start_date' => $exam->start_date ? Carbon::parse($exam->start_date)->format('Y-m-d') : null,
                'end_date' => $exam->end_date ? Carbon::parse($exam->end_date)->format('Y-m-d') : null,
                'specialty_name' => $exam->schoolYear?->specialty?->specialty_name,
                'level_name' => $exam->schoolYear?->specialty?->level?->name,
                'candidate_count' => AccessedStudent::where('school_branch_id', $currentSchool->id)->where('exam_id', $exam->id)->count(),
            ],
            'courses' => $courses->map(fn($course) => [
                'course_id' => $course->id,
                'course_title' => $course->course_title,
                'course_code' => $course->course_code,
                'is_joint' => $jointSlots->has($course->id),
                'joint' => $jointSlots->get($course->id),
            ])->values()->all(),
            'halls' => Hall::where('school_branch_id', $currentSchool->id)
                ->where('status', 'available')
                ->whereHas('types', fn($query) => $query->where('key', 'exam.hall'))
                ->orderBy('name')
                ->get()
                ->map(fn(Hall $hall) => ['hall_id' => $hall->id, 'hall_name' => $hall->name, 'capacity' => (int) $hall->capacity])
                ->values()
                ->all(),
            'invigilators' => $invigilators->map(fn(ExamInvigilator $invigilator) => [
                'invigilator_id' => $invigilator->id,
                'name' => $invigilator->invigilatable?->name,
                'type' => $invigilator->actorable_type === Teacher::class ? 'teacher' : 'school_admin',
                'courses_taught' => $invigilator->actorable_type === Teacher::class
                    ? ($taught[$invigilator->actorable_id] ?? collect())->pluck('course_id')->unique()->values()->all()
                    : [],
            ])->values()->all(),
        ];
    }
}
