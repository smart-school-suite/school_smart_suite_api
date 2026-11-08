<?php

namespace App\Jobs\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use App\Exceptions\AppException;
use App\Jobs\ExamTimetable\Concerns\BroadcastsExamTimetableGeneration;
use App\Jobs\ExamTimetable\Concerns\CollectsExamUserErrors;
use App\Jobs\ExamTimetable\Concerns\TracksExamVersionStatus;
use App\Models\AccessedStudent as ExamCandidate;
use App\Models\Course\CourseSpecialty;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\Exams;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamTimetable;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Hall;
use App\Models\Job\SystemJob;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Services\ExamTimetable\Support\ExamActiveCommitments;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Generates one version of an exam timetable, following the semester
 * timetable flow: gather the data (stopping early on setup problems a user
 * must fix), build the scheduler input, run the engine, store the result in
 * Mongo, then update the version and broadcast the outcome.
 */
class GenerateExamTimetable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use BroadcastsExamTimetableGeneration, TracksExamVersionStatus, CollectsExamUserErrors;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 30;

    protected const ERROR = "error";
    protected const EXAM_HALL_TYPE = "exam.hall";

    public function __construct(
        protected readonly object $currentSchool,
        protected readonly array $payload,
        protected readonly string $jobId,
    ) {}

    public function handle(): void
    {
        $systemJob = SystemJob::with('initiatedBy')->find($this->jobId);
        if (!$systemJob) {
            Log::warning("GenerateExamTimetable: SystemJob [{$this->jobId}] not found. Aborting.");
            return;
        }

        $exam = Exams::where('school_branch_id', $this->currentSchool->id)
            ->with(['examtype.semesters', 'schoolYear.specialty.level'])
            ->where('id', $this->payload['exam_id'])
            ->firstOrFail();

        try {
            $this->process($systemJob, $exam);
        } catch (AppException $e) {
            $this->failJob($systemJob, $e->getMessage(), $e->getCode(), ExamTimetableVersion::STATUS_FAILED);
            $this->broadcastFailure($systemJob, $e);
            $this->fail($e);
        } catch (Throwable $e) {
            $this->failJob($systemJob, $e->getMessage(), 500);
            $this->broadcastFailure($systemJob, $e);
            $this->fail($e);
        }
    }

    private function process(SystemJob $systemJob, Exams $exam): void
    {
        $this->updateJobProgress($systemJob, 'PROCESSING', 'Gathering Data', 10);

        $branchId = $this->currentSchool->id;
        $versionId = $this->startVersion($exam->id);
        $specialty = $exam->schoolYear?->specialty;
        $semesterId = $exam->examtype?->semester_id;

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Gathering Data....',
            'percentage' => 10,
            'details' => 'Fetching courses, candidates, halls, invigilators and constraints to prepare the exam timetable.',
        ]);

        [$startDate, $endDate] = $this->getExamPeriod($exam);

        $courses = $specialty ? $this->getCourses($branchId, $specialty, $semesterId) : collect();
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam courses fetched']);

        $candidateCount = $this->getCandidateCount($branchId, $exam);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam candidates counted']);

        $halls = $this->getHalls($branchId, $candidateCount);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam halls fetched']);

        $invigilators = $this->getInvigilators($branchId, $exam);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Invigilators fetched']);

        $jointCourses = $this->getJointCourses($branchId, $exam, $courses);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Joint courses fetched']);

        if (!$specialty) {
            $this->addUserError(
                'Exam Has No Specialty',
                'This exam is not linked to a specialty, so its courses and candidates cannot be found.',
                ['exam_id' => $exam->id, 'reason' => 'exam_without_specialty', 'path' => '/exams']
            );
        }

        $this->failOnUserErrors($systemJob, $versionId, $exam->id);

        $busyPeriods = app(ExamActiveCommitments::class)->busyPeriodsForGeneration(
            $branchId,
            $exam->id,
            $startDate,
            $endDate,
            $halls->pluck('id')->all(),
            $invigilators,
        );
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Hall and invigilator busy periods fetched']);

        $body = $this->buildRequestBody(
            $exam,
            $specialty->id,
            $startDate,
            $endDate,
            $candidateCount,
            $courses,
            $halls,
            $invigilators,
            $busyPeriods,
            $jointCourses,
        );

        $this->updateJobProgress($systemJob, 'PROCESSING', 'Generating Timetable', 50);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Generating Timetable....',
            'percentage' => 50,
            'details' => 'Placing every exam with halls and invigilators within the constraints.',
        ]);

        $response = app(SchedularEngine::class)->run($body);

        ExamTimetable::create([
            'exam_id' => $exam->id,
            'timetable_version_id' => $versionId,
            'school_branch_id' => $branchId,
            'status' => $response->status,
            'timetable_slots' => $response->timetable,
            'request_payload' => $this->payload,
            'scheduler_input' => $body,
            'raw_diagnostics' => $this->diagnosticsToArray($response),
            'raw_suggestions' => $response->suggestions,
        ]);

        ExamTimetableVersion::find($versionId)?->update(['scheduler_status' => $response->status]);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Interpreting Results....',
            'percentage' => 90,
            'details' => 'Interpreting the results of the exam timetable generation.',
            'version' => ExamTimetableVersion::find($versionId),
        ]);

        $this->updateJobProgress($systemJob, $this->isErrorResponse($response) ? 'FAILED' : 'COMPLETED', 'Done', 100);
        $this->broadcastCompletion($systemJob, $response, $versionId);
    }

    // ── Data gathering ───────────────────────────────────────────────────

    private function getExamPeriod(Exams $exam): array
    {
        $start = $exam->start_date ? Carbon::parse($exam->start_date)->format('Y-m-d') : null;
        $end = $exam->end_date ? Carbon::parse($exam->end_date)->format('Y-m-d') : null;

        if (!$start || !$end) {
            $this->addUserError(
                'Exam Has No Dates',
                'Set the start and end dates of the exam before generating its timetable.',
                ['exam_id' => $exam->id, 'reason' => 'exam_without_dates', 'path' => '/exams']
            );
        } elseif ($start > $end) {
            $this->addUserError(
                'Exam Dates Are Reversed',
                "The exam starts on {$start} but ends on {$end}. Fix the exam dates before generating its timetable.",
                ['exam_id' => $exam->id, 'start_date' => $start, 'end_date' => $end, 'reason' => 'exam_dates_reversed', 'path' => '/exams']
            );
        }

        return [$start, $end];
    }

    private function getCourses(string $branchId, object $specialty, ?string $semesterId): Collection
    {
        $courses = CourseSpecialty::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialty->id)
            ->whereHas('course', fn($query) => $query
                ->where('school_branch_id', $branchId)
                ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId)))
            ->with('course')
            ->get()
            ->pluck('course')
            ->filter()
            ->unique('id')
            ->values();

        if ($courses->isEmpty()) {
            $this->addUserError(
                'No Courses Found',
                "No courses are assigned to {$specialty->specialty_name} for this exam's semester. Assign courses to the specialty before generating the exam timetable.",
                ['specialty_id' => $specialty->id, 'semester_id' => $semesterId, 'reason' => 'no_courses', 'path' => '/courses']
            );
            return collect();
        }

        $active = $courses->filter(fn($course) => ($course->status ?? 'active') === 'active')->values();

        if ($active->isEmpty()) {
            $this->addUserError(
                'No Active Courses',
                "All {$courses->count()} course(s) of {$specialty->specialty_name} for this semester are deactivated. Activate the courses to be examined.",
                ['specialty_id' => $specialty->id, 'course_names' => $courses->pluck('course_title')->all(), 'reason' => 'no_active_courses', 'path' => '/courses']
            );
        }

        return $active;
    }

    private function getCandidateCount(string $branchId, Exams $exam): int
    {
        $count = ExamCandidate::where('school_branch_id', $branchId)
            ->where('exam_id', $exam->id)
            ->count();

        if ($count === 0) {
            $this->addUserError(
                'No Exam Candidates',
                'No students are registered as candidates for this exam, so there is nobody to seat. Add the exam candidates first.',
                ['exam_id' => $exam->id, 'reason' => 'no_candidates', 'path' => '/exam-candidates']
            );
        }

        return $count;
    }

    private function getHalls(string $branchId, int $candidateCount): Collection
    {
        $halls = Hall::where('school_branch_id', $branchId)
            ->where('status', 'available')
            ->whereHas('types', fn($query) => $query->where('key', self::EXAM_HALL_TYPE))
            ->get();

        if ($halls->isEmpty()) {
            $this->addUserError(
                'No Exam Halls',
                "No available hall is marked as an exam hall. Give at least one hall the exam hall type before generating the exam timetable.",
                ['reason' => 'no_exam_halls', 'path' => '/hall']
            );
            return $halls;
        }

        $seats = (int) $halls->sum('capacity');
        if ($candidateCount > 0 && $seats < $candidateCount) {
            $this->addUserError(
                'Not Enough Exam Seats',
                "The exam halls seat {$seats} candidate(s) in total but the exam has {$candidateCount}. Add exam halls or raise their capacity.",
                ['total_seats' => $seats, 'candidate_count' => $candidateCount, 'reason' => 'not_enough_seats', 'path' => '/hall']
            );
        }

        return $halls;
    }

    private function getInvigilators(string $branchId, Exams $exam): Collection
    {
        $invigilators = ExamInvigilator::where('school_branch_id', $branchId)
            ->where('exam_id', $exam->id)
            ->with('invigilatable')
            ->get()
            ->filter(fn(ExamInvigilator $invigilator) => $invigilator->invigilatable !== null)
            ->values();

        if ($invigilators->isEmpty()) {
            $this->addUserError(
                'No Invigilators',
                'No invigilators are assigned to this exam. Assign invigilators before generating its timetable.',
                ['exam_id' => $exam->id, 'reason' => 'no_invigilators', 'path' => '/exam-invigilators']
            );
        }

        return $invigilators;
    }

    /** Courses each invigilator teaches; they never invigilate those exams. */
    private function coursesTaught(string $branchId, Collection $invigilators): array
    {
        $teacherIds = $invigilators
            ->filter(fn(ExamInvigilator $invigilator) => $invigilator->actorable_type === Teacher::class)
            ->pluck('actorable_id')
            ->all();

        $taught = TeacherCoursePreference::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->get(['teacher_id', 'course_id'])
            ->groupBy('teacher_id')
            ->map(fn(Collection $rows) => $rows->pluck('course_id')->unique()->values()->all());

        return $invigilators->mapWithKeys(fn(ExamInvigilator $invigilator) => [
            $invigilator->id => $invigilator->actorable_type === Teacher::class
                ? ($taught[$invigilator->actorable_id] ?? [])
                : [],
        ])->all();
    }

    private function getJointCourses(string $branchId, Exams $exam, Collection $courses): array
    {
        $refs = ExamJointCourseRef::where('school_branch_id', $branchId)
            ->where('exam_id', $exam->id)
            ->with(['examJointCourse.course', 'examJointCourse.examJointCourseSlot'])
            ->get();

        $periods = [];

        foreach ($refs as $ref) {
            $jointCourse = $ref->examJointCourse;
            if (!$jointCourse) {
                continue;
            }

            if ($jointCourse->examJointCourseSlot->isEmpty()) {
                $title = $jointCourse->course?->course_title ?? 'A joint course';
                $this->addUserError(
                    'Joint Course Has No Time',
                    "{$title} is shared with other specialties but has no exam time set yet. Set its time on the joint exam timetable page, then generate again.",
                    ['course_id' => $jointCourse->course_id, 'exam_joint_course_id' => $jointCourse->id, 'reason' => 'joint_course_without_slots', 'path' => '/exam-joint-course-timetable']
                );
                continue;
            }

            foreach ($jointCourse->examJointCourseSlot as $slot) {
                $periods[] = [
                    'course_id' => $jointCourse->course_id,
                    'date' => Carbon::parse($slot->date)->format('Y-m-d'),
                    'start_time' => Time::normalize((string) $slot->getRawOriginal('start_time')),
                    'end_time' => Time::normalize((string) $slot->getRawOriginal('end_time')),
                ];
            }
        }

        return $periods;
    }

    // ── Scheduler input ──────────────────────────────────────────────────

    private function buildRequestBody(
        Exams $exam,
        string $specialtyId,
        string $startDate,
        string $endDate,
        int $candidateCount,
        Collection $courses,
        Collection $halls,
        Collection $invigilators,
        array $busyPeriods,
        array $jointCourses,
    ): array {
        $taught = $this->coursesTaught($this->currentSchool->id, $invigilators);

        return [
            'exam_id' => $exam->id,
            'specialty_id' => $specialtyId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'candidate_count' => $candidateCount,
            'candidates_per_invigilator' => $this->payload['candidates_per_invigilator'] ?? null,
            'courses' => $courses->map(fn($course) => [
                'course_id' => $course->id,
                'course_title' => $course->course_title,
                'course_code' => $course->course_code,
            ])->values()->all(),
            'halls' => $halls->map(fn(Hall $hall) => [
                'hall_id' => $hall->id,
                'hall_name' => $hall->name,
                'capacity' => (int) $hall->capacity,
            ])->values()->all(),
            'invigilators' => $invigilators->map(fn(ExamInvigilator $invigilator) => [
                'invigilator_id' => $invigilator->id,
                'name' => $invigilator->invigilatable->name ?? null,
                'courses_taught' => $taught[$invigilator->id] ?? [],
            ])->values()->all(),
            'hall_busy_periods' => $busyPeriods['hall_busy_periods'],
            'invigilator_busy_periods' => $busyPeriods['invigilator_busy_periods'],
            'hard_constraints' => array_filter([
                OperationalPeriod::KEY => $this->payload[OperationalPeriod::KEY] ?? null,
                SessionDuration::KEY => $this->payload[SessionDuration::KEY] ?? null,
                RequiredJointCourseSession::KEY => $jointCourses ?: null,
            ], fn($value) => !is_null($value)),
            'soft_constraints' => array_filter([
                RequestedAssignment::KEY => $this->payload[RequestedAssignment::KEY] ?? null,
                CourseTimeRequest::KEY => $this->payload[CourseTimeRequest::KEY] ?? null,
                HallRequestedSlot::KEY => $this->payload[HallRequestedSlot::KEY] ?? null,
                InvigilatorRequestedSlot::KEY => $this->payload[InvigilatorRequestedSlot::KEY] ?? null,
                StudentDailyLoadRange::KEY => $this->payload[StudentDailyLoadRange::KEY] ?? null,
            ], fn($value) => !is_null($value)),
        ];
    }

    private function diagnosticsToArray(ResponseDTO $response): array
    {
        return [
            'hard' => $response->diagnostics['hard']->map(fn(DiagnosticDTO $diagnostic) => $diagnostic->toArray())->values()->all(),
            'soft' => $response->diagnostics['soft']->map(fn(DiagnosticDTO $diagnostic) => $diagnostic->toArray())->values()->all(),
        ];
    }

    // ── Job bookkeeping ──────────────────────────────────────────────────

    private function isErrorResponse(object $response): bool
    {
        return strtolower($response->status ?? self::ERROR) === self::ERROR;
    }

    private function updateJobProgress(SystemJob $systemJob, string $status, string $stage, int $progress): void
    {
        $systemJob->update([
            'status' => $status,
            'stage' => $stage,
            'progress' => $progress,
            'finished_at' => in_array($status, ['COMPLETED', 'FAILED'], true) ? Carbon::now() : null,
            'updated_at' => Carbon::now(),
        ]);
    }

    private function failJob(SystemJob $systemJob, string $message, int|string $code, string $versionStatus = ExamTimetableVersion::STATUS_ERROR): void
    {
        $systemJob->update([
            'status' => 'FAILED',
            'stage' => 'Failed',
            'progress' => 0,
            'error_code' => (string) $code,
            'error_message' => $message,
            'finished_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $systemJob->systemJobEvent()->create([
            'event_type' => 'error',
            'message' => $message,
        ]);

        $this->finishVersion($versionStatus);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("GenerateExamTimetable job [{$this->jobId}] permanently failed.", [
            'exception' => $exception->getMessage(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'school_id' => $this->currentSchool->id,
            'payload' => $this->payload,
        ]);

        $systemJob = SystemJob::with('initiatedBy')->find($this->jobId);

        if ($systemJob) {
            $alreadyReported = $systemJob->status === 'FAILED';
            $this->failJob($systemJob, $exception->getMessage(), 500);

            if (!$alreadyReported) {
                $this->broadcastFailure($systemJob, $exception);
            }
        }
    }
}
