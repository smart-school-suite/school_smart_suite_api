<?php

namespace App\Jobs\ResitTimetable;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use App\Events\ResitTimetable\ResitTimetableGenerationEvent;
use App\Exceptions\AppException;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\Job\SystemJob;
use App\Models\ResitExam;
use App\Models\ResitTimetable\ResitInvigilator;
use App\Models\ResitTimetable\ResitTimetable;
use App\Models\ResitTimetable\ResitTimetableError;
use App\Models\ResitTimetable\ResitTimetableVersion;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Services\ResitTimetable\Support\JointResitCourses;
use App\Services\ResitTimetable\Support\ResitActiveCommitments;
use App\Services\ResitTimetable\Support\ResitCandidates;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Generates one version of a resit timetable with the exam timetable
 * scheduler. A resit exam differs from an exam in who sits what: each course
 * has its own candidates (the students resitting it), so the scheduler gets
 * every course's student ids and only keeps apart the exams that share a
 * candidate. On top of the exam rules it keeps:
 *
 *  - candidates who sit another active exam or resit timetable at a time are
 *    not given a resit then;
 *  - halls and invigilators busy in any active exam or resit timetable stay busy;
 *  - a course resat by several specialties (same academic year and resit type)
 *    is examined at the time another specialty's active resit timetable
 *    already gives it, so the paper is sat once.
 */
class GenerateResitTimetable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 30;

    protected const EXAM_HALL_TYPE = 'exam.hall';

    protected ?string $versionId = null;
    protected bool $finalEventBroadcasted = false;
    private array $userErrors = [];

    public function __construct(
        protected readonly object $currentSchool,
        protected readonly array $payload,
        protected readonly string $jobId,
    ) {}

    public function handle(): void
    {
        $systemJob = SystemJob::with('initiatedBy')->find($this->jobId);
        if (!$systemJob) {
            Log::warning("GenerateResitTimetable: SystemJob [{$this->jobId}] not found. Aborting.");
            return;
        }

        $resitExam = ResitExam::where('school_branch_id', $this->currentSchool->id)
            ->with(['examType', 'schoolYear.specialty.level'])
            ->where('id', $this->payload['resit_exam_id'])
            ->firstOrFail();

        try {
            $this->process($systemJob, $resitExam);
        } catch (AppException $e) {
            $this->failJob($systemJob, $e->getMessage(), $e->getCode(), ResitTimetableVersion::STATUS_FAILED);
            $this->broadcastFailure($systemJob, $e);
            $this->fail($e);
        } catch (Throwable $e) {
            $this->failJob($systemJob, $e->getMessage(), 500);
            $this->broadcastFailure($systemJob, $e);
            $this->fail($e);
        }
    }

    private function process(SystemJob $systemJob, ResitExam $resitExam): void
    {
        $this->updateJobProgress($systemJob, 'PROCESSING', 'Gathering Data', 10);

        $branchId = $this->currentSchool->id;
        $versionId = $this->startVersion($resitExam->id);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Gathering Data....',
            'percentage' => 10,
            'details' => 'Fetching resit courses, candidates, halls, invigilators and constraints to prepare the resit timetable.',
        ]);

        [$startDate, $endDate] = $this->getPeriod($resitExam);

        $candidates = app(ResitCandidates::class)->byCourse($branchId, $resitExam);
        $courses = $this->getCourses($branchId, $candidates);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Resit courses and candidates fetched']);

        $studentIds = collect($candidates)->flatten()->unique()->values()->all();
        $largestCourse = $courses->max(fn($course) => count($candidates[$course->id] ?? [])) ?? 0;

        $halls = $this->getHalls($branchId, (int) $largestCourse);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam halls fetched']);

        $invigilators = $this->getInvigilators($branchId, $resitExam);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Invigilators fetched']);

        $this->failOnUserErrors($systemJob, $versionId, $resitExam->id);

        $busy = app(ResitActiveCommitments::class)->busyPeriodsForGeneration(
            $branchId,
            $resitExam->id,
            $startDate,
            $endDate,
            $halls->pluck('id')->all(),
            $invigilators,
            $studentIds,
        );
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Hall, invigilator and candidate busy periods fetched']);

        $jointCourses = app(JointResitCourses::class)->periodsFor($branchId, $resitExam, $courses->pluck('id')->all(), $startDate, $endDate);

        $body = $this->buildRequestBody($resitExam, $startDate, $endDate, $studentIds, $candidates, $courses, $halls, $invigilators, $busy, $jointCourses);

        $this->updateJobProgress($systemJob, 'PROCESSING', 'Generating Timetable', 50);
        $this->broadcastGeneration($systemJob, [
            'stage' => 'Generating Timetable....',
            'percentage' => 50,
            'details' => 'Placing every resit with halls and invigilators, keeping apart the resits a candidate sits.',
        ]);

        $response = app(SchedularEngine::class)->run($body);

        ResitTimetable::create([
            'resit_exam_id' => $resitExam->id,
            'timetable_version_id' => $versionId,
            'school_branch_id' => $branchId,
            'status' => $response->status,
            'timetable_slots' => $response->timetable,
            'request_payload' => $this->payload,
            'scheduler_input' => $body,
            'raw_diagnostics' => $this->diagnosticsToArray($response),
            'raw_suggestions' => $response->suggestions,
        ]);

        ResitTimetableVersion::find($versionId)?->update(['scheduler_status' => $response->status]);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Interpreting Results....',
            'percentage' => 90,
            'details' => 'Interpreting the results of the resit timetable generation.',
            'version' => ResitTimetableVersion::find($versionId),
        ]);

        $this->updateJobProgress($systemJob, strtolower($response->status ?? 'error') === 'error' ? 'FAILED' : 'COMPLETED', 'Done', 100);
        $this->broadcastCompletion($systemJob, $response, $versionId);
    }

    // ── Data gathering ───────────────────────────────────────────────────

    private function getPeriod(ResitExam $resitExam): array
    {
        $start = $resitExam->start_date ? Carbon::parse($resitExam->start_date)->format('Y-m-d') : null;
        $end = $resitExam->end_date ? Carbon::parse($resitExam->end_date)->format('Y-m-d') : null;

        if (!$start || !$end) {
            $this->addUserError(
                'Resit Exam Has No Dates',
                'Set the start and end dates of the resit exam before generating its timetable.',
                ['resit_exam_id' => $resitExam->id, 'reason' => 'resit_exam_without_dates', 'path' => '/resit-exams']
            );
        } elseif ($start > $end) {
            $this->addUserError(
                'Resit Exam Dates Are Reversed',
                "The resit exam starts on {$start} but ends on {$end}. Fix its dates before generating its timetable.",
                ['resit_exam_id' => $resitExam->id, 'start_date' => $start, 'end_date' => $end, 'reason' => 'resit_exam_dates_reversed', 'path' => '/resit-exams']
            );
        }

        return [$start, $end];
    }

    /** Courses somebody resits, with their candidates. */
    private function getCourses(string $branchId, array $candidates): Collection
    {
        if (empty($candidates)) {
            $this->addUserError(
                'No Resit Candidates',
                'No student is registered to resit a course in this resit exam, so there is nothing to schedule.',
                ['reason' => 'no_resit_candidates', 'path' => '/resit-candidates']
            );
            return collect();
        }

        $courses = Courses::where('school_branch_id', $branchId)
            ->whereIn('id', array_keys($candidates))
            ->get();

        $missing = array_diff(array_keys($candidates), $courses->pluck('id')->all());
        if ($missing) {
            $this->addUserError(
                'Resit Courses Not Found',
                count($missing) . ' course(s) students resit no longer exist. Remove those resits or restore the courses.',
                ['course_ids' => array_values($missing), 'reason' => 'resit_courses_missing', 'path' => '/courses']
            );
        }

        return $courses->values();
    }

    private function getHalls(string $branchId, int $largestCourse): Collection
    {
        $halls = Hall::where('school_branch_id', $branchId)
            ->where('status', 'available')
            ->whereHas('types', fn($query) => $query->where('key', self::EXAM_HALL_TYPE))
            ->get();

        if ($halls->isEmpty()) {
            $this->addUserError(
                'No Exam Halls',
                'No available hall is marked as an exam hall. Give at least one hall the exam hall type before generating the resit timetable.',
                ['reason' => 'no_exam_halls', 'path' => '/hall']
            );
            return $halls;
        }

        $seats = (int) $halls->sum('capacity');
        if ($largestCourse > 0 && $seats < $largestCourse) {
            $this->addUserError(
                'Not Enough Exam Seats',
                "The exam halls seat {$seats} candidate(s) in total but one resit has {$largestCourse}. Add exam halls or raise their capacity.",
                ['total_seats' => $seats, 'candidate_count' => $largestCourse, 'reason' => 'not_enough_seats', 'path' => '/hall']
            );
        }

        return $halls;
    }

    private function getInvigilators(string $branchId, ResitExam $resitExam): Collection
    {
        $invigilators = ResitInvigilator::where('school_branch_id', $branchId)
            ->where('resit_exam_id', $resitExam->id)
            ->with('invigilatable')
            ->get()
            ->filter(fn(ResitInvigilator $invigilator) => $invigilator->invigilatable !== null)
            ->values();

        if ($invigilators->isEmpty()) {
            $this->addUserError(
                'No Invigilators',
                'No invigilators are assigned to this resit exam. Assign invigilators before generating its timetable.',
                ['resit_exam_id' => $resitExam->id, 'reason' => 'no_invigilators', 'path' => '/resit-invigilators']
            );
        }

        return $invigilators;
    }

    /** Courses each invigilator teaches; they never invigilate those resits. */
    private function coursesTaught(string $branchId, Collection $invigilators): array
    {
        $teacherIds = $invigilators
            ->filter(fn(ResitInvigilator $invigilator) => $invigilator->actorable_type === Teacher::class)
            ->pluck('actorable_id')
            ->all();

        $taught = TeacherCoursePreference::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->get(['teacher_id', 'course_id'])
            ->groupBy('teacher_id')
            ->map(fn(Collection $rows) => $rows->pluck('course_id')->unique()->values()->all());

        return $invigilators->mapWithKeys(fn(ResitInvigilator $invigilator) => [
            $invigilator->id => $invigilator->actorable_type === Teacher::class
                ? ($taught[$invigilator->actorable_id] ?? [])
                : [],
        ])->all();
    }

    // ── Scheduler input ──────────────────────────────────────────────────

    private function buildRequestBody(
        ResitExam $resitExam,
        string $startDate,
        string $endDate,
        array $studentIds,
        array $candidates,
        Collection $courses,
        Collection $halls,
        Collection $invigilators,
        array $busy,
        array $jointCourses,
    ): array {
        $taught = $this->coursesTaught($this->currentSchool->id, $invigilators);

        return [
            'exam_id' => $resitExam->id,
            'resit_exam_id' => $resitExam->id,
            'specialty_id' => $resitExam->schoolYear?->specialty_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'candidate_count' => count($studentIds),
            'candidates_per_invigilator' => $this->payload['candidates_per_invigilator'] ?? null,
            'courses' => $courses->map(fn($course) => [
                'course_id' => $course->id,
                'course_title' => $course->course_title,
                'course_code' => $course->course_code,
                'candidate_count' => count($candidates[$course->id] ?? []),
                'student_ids' => $candidates[$course->id] ?? [],
            ])->values()->all(),
            'halls' => $halls->map(fn(Hall $hall) => [
                'hall_id' => $hall->id,
                'hall_name' => $hall->name,
                'capacity' => (int) $hall->capacity,
            ])->values()->all(),
            'invigilators' => $invigilators->map(fn(ResitInvigilator $invigilator) => [
                'invigilator_id' => $invigilator->id,
                'name' => $invigilator->invigilatable->name ?? null,
                'courses_taught' => $taught[$invigilator->id] ?? [],
            ])->values()->all(),
            'hall_busy_periods' => $busy['hall_busy_periods'],
            'invigilator_busy_periods' => $busy['invigilator_busy_periods'],
            'candidate_busy_periods' => $busy['candidate_busy_periods'],
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

    // ── Setup errors ─────────────────────────────────────────────────────

    private function addUserError(string $title, string $description, array $params = []): void
    {
        $this->userErrors[] = ['title' => $title, 'description' => $description, 'additional_params' => $params];
    }

    private function failOnUserErrors(SystemJob $systemJob, string $versionId, string $resitExamId): void
    {
        if (empty($this->userErrors)) {
            return;
        }

        $errors = $this->userErrors;
        $errorCount = count($errors);

        ResitTimetableError::create([
            'id' => Str::uuid()->toString(),
            'resit_exam_id' => $resitExamId,
            'timetable_version_id' => $versionId,
            'school_branch_id' => $this->currentSchool->id,
            'status' => 'failed',
            'request_payload' => $this->payload,
            'errors' => $errors,
        ]);

        $version = ResitTimetableVersion::find($versionId);
        $version?->update(['scheduler_status' => ResitTimetableVersion::STATUS_FAILED]);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Validation Failed',
            'percentage' => 100,
            'status' => 'failed',
            'details' => "Resit timetable generation failed due to {$errorCount} issue(s)",
            'errors' => $errors,
            'error_count' => $errorCount,
            'version' => $version,
        ]);

        $this->finalEventBroadcasted = true;

        throw new AppException(
            "Validation Process Failed: {$errorCount} error(s) found",
            422,
            'Validation Error',
            'Please fix the following issues'
        );
    }

    // ── Version ──────────────────────────────────────────────────────────

    private function startVersion(string $resitExamId): string
    {
        $version = !empty($this->payload['version_id'])
            ? ResitTimetableVersion::where('school_branch_id', $this->currentSchool->id)->find($this->payload['version_id'])
            : null;

        $version ??= ResitTimetableVersion::createNext($this->currentSchool->id, $resitExamId, ResitTimetableVersion::STATUS_GENERATING);
        $version->update(['scheduler_status' => ResitTimetableVersion::STATUS_GENERATING]);

        return $this->versionId = $version->id;
    }

    private function finishVersion(string $status): void
    {
        $versionId = $this->versionId ?? $this->payload['version_id'] ?? null;
        if (!$versionId) {
            return;
        }

        ResitTimetableVersion::where('school_branch_id', $this->currentSchool->id)
            ->where('id', $versionId)
            ->where('scheduler_status', ResitTimetableVersion::STATUS_GENERATING)
            ->update(['scheduler_status' => $status]);
    }

    // ── Broadcasting ─────────────────────────────────────────────────────

    private function broadcastGeneration(SystemJob $systemJob, array $payload): void
    {
        $actor = $systemJob->initiatedBy;
        if (!$actor) {
            return;
        }

        $payload['version_id'] ??= $this->versionId ?? ($this->payload['version_id'] ?? null);

        try {
            ResitTimetableGenerationEvent::dispatch($actor, $this->currentSchool, $payload);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function broadcastCompletion(SystemJob $systemJob, ResponseDTO $response, string $versionId): void
    {
        $schedulerStatus = Str::lower($response->status ?? 'error');
        $hardCount = count($response->diagnostics['hard'] ?? []);
        $softCount = count($response->diagnostics['soft'] ?? []);

        [$status, $stage, $details] = match ($schedulerStatus) {
            'optimal' => ['completed', 'Resit Timetable Generated', 'The resit timetable was generated successfully with no conflicts.'],
            'partial' => ['partial', 'Resit Timetable Generated With Warnings', "The resit timetable was generated, but {$softCount} request(s) could not be met."],
            default => ['failed', 'Resit Timetable Generation Failed', "The resit timetable could not be generated because {$hardCount} required constraint(s) could not be met."],
        };

        $this->finalEventBroadcasted = true;

        $this->broadcastGeneration($systemJob, [
            'stage' => $stage,
            'percentage' => 100,
            'status' => $status,
            'scheduler_status' => $schedulerStatus,
            'details' => $details,
            'hard_diagnostic_count' => $hardCount,
            'soft_diagnostic_count' => $softCount,
            'unplaced_course_count' => count($response->unplaced_courses ?? []),
            'version' => ResitTimetableVersion::find($versionId),
        ]);
    }

    private function broadcastFailure(SystemJob $systemJob, Throwable $exception): void
    {
        if ($this->finalEventBroadcasted) {
            return;
        }
        $this->finalEventBroadcasted = true;

        $versionId = $this->versionId ?? $this->payload['version_id'] ?? null;

        $this->broadcastGeneration($systemJob, [
            'stage' => $exception instanceof AppException && $exception->getTitle() ? $exception->getTitle() : 'Resit Timetable Generation Failed',
            'percentage' => 100,
            'status' => 'failed',
            'scheduler_status' => $exception instanceof AppException ? ResitTimetableVersion::STATUS_FAILED : ResitTimetableVersion::STATUS_ERROR,
            'details' => $exception instanceof AppException
                ? ($exception->getDescription() ?: $exception->getMessage())
                : 'Something went wrong while generating the resit timetable. Please try again.',
            'version' => $versionId ? ResitTimetableVersion::find($versionId) : null,
        ]);
    }

    // ── Job bookkeeping ──────────────────────────────────────────────────

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

    private function failJob(SystemJob $systemJob, string $message, int|string $code, string $versionStatus = ResitTimetableVersion::STATUS_ERROR): void
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

        $systemJob->systemJobEvent()->create(['event_type' => 'error', 'message' => $message]);

        $this->finishVersion($versionStatus);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("GenerateResitTimetable job [{$this->jobId}] permanently failed.", [
            'exception' => $exception->getMessage(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'school_id' => $this->currentSchool->id,
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
