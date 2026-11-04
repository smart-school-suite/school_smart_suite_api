<?php

namespace App\Jobs\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Exceptions\AppException;
use App\Jobs\ExamTimetable\Concerns\BroadcastsExamTimetableGeneration;
use App\Jobs\ExamTimetable\Concerns\TracksExamVersionStatus;
use App\Models\Course\CourseSpecialty;
use App\Models\Exam\ExamCandidate;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\Exams;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamTimetableError;
use App\Models\ExamTimetable\ExamTimetableRun;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Hall;
use App\Models\Job\SystemJob;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Solution\Support\ExamPayload;
use App\Schedular\ExamTimetable\Support\Time;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Generates one exam timetable version.
 *
 * Gathers the exam's courses, candidates, halls, invigilators and joint course sessions, plus the halls and
 * invigilators already booked by other active exam timetables, then runs the exam scheduler. Missing setup
 * (no courses, candidates, halls or invigilators) is stored as setup errors and the version is "failed".
 * Otherwise the result, diagnostics and solution paths are stored on an ExamTimetableRun document and the
 * version gets the scheduler status (optimal, partial or error).
 */
class GenerateFixedExamTimetable implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels, BroadcastsExamTimetableGeneration, TracksExamVersionStatus;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 30;

    protected Collection $userErrors;

    public function __construct(
        protected readonly object $currentSchool,
        protected readonly array $payload,
        protected readonly string $jobId,
    ) {
        $this->userErrors = collect();
    }

    public function handle(): void
    {
        $systemJob = SystemJob::with('initiatedBy')->find($this->jobId);

        if (!$systemJob) {
            Log::warning("GenerateFixedExamTimetable: SystemJob [{$this->jobId}] not found. Aborting.");
            return;
        }

        $this->userErrors = collect();

        try {
            $exam = Exams::where('school_branch_id', $this->currentSchool->id)
                ->with(['schoolYear.specialty.level', 'examtype'])
                ->findOrFail($this->payload['exam_id']);

            $this->process($systemJob, $exam);
        } catch (AppException $e) {
            $this->failJob($systemJob, $e->getMessage(), $e->getCode(), ExamTimetableVersion::STATUS_FAILED);
            $this->broadcastFailure($systemJob, $e);
            $this->fail($e);
        } catch (Throwable $e) {
            report($e);
            $this->failJob($systemJob, $e->getMessage(), 500);
            $this->broadcastFailure($systemJob, $e);
            $this->fail($e);
        }
    }

    private function process(SystemJob $systemJob, Exams $exam): void
    {
        $this->updateJobProgress($systemJob, 'PROCESSING', 'Gathering Data', 10);
        $versionId = $this->startVersion($exam->id);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Gathering Data....',
            'percentage' => 10,
            'details' => 'Fetching courses, candidates, halls, invigilators and constraints to prepare the exam timetable.',
        ]);

        $branchId = $this->currentSchool->id;
        $specialty = $exam->schoolYear?->specialty;

        if (!$exam->start_date || !$exam->end_date) {
            $this->addUserError('Exam Dates Missing', 'This exam has no start or end date, so there are no dates to schedule it on. Set the exam dates first.', [
                'exam_id' => $exam->id,
                'reason' => 'no_exam_dates',
                'path' => '/exams',
            ]);
        }

        if (!$specialty) {
            $this->addUserError('Exam Has No Specialty', 'This exam is not linked to a specialty through its school year, so its courses and candidates cannot be found.', [
                'exam_id' => $exam->id,
                'reason' => 'no_specialty',
                'path' => '/exams',
            ]);
        }

        $courses = $specialty ? $this->getCourses($branchId, $exam, $specialty) : collect();
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam courses fetched']);

        $candidateCount = $this->getCandidateCount($branchId, $exam);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam candidates counted']);

        $halls = $this->getHalls($branchId);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam halls fetched']);

        $invigilators = $this->getInvigilators($branchId, $exam);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam invigilators fetched']);

        if ($this->userErrors->isNotEmpty()) {
            $this->createUserError($versionId, $exam->id);
            $count = $this->userErrors->count();

            $this->broadcastGeneration($systemJob, [
                'stage' => 'Validation Failed',
                'percentage' => 100,
                'status' => 'failed',
                'details' => "Exam timetable generation failed due to {$count} issue(s)",
                'errors' => $this->userErrors->toArray(),
                'error_count' => $count,
                'version' => ExamTimetableVersion::find($versionId),
            ]);

            $this->finalEventBroadcasted = true;

            throw new AppException(
                "Validation Process Failed: {$count} error(s) found",
                422,
                'Validation Error',
                'Please fix the following issues'
            );
        }

        [$hallBusy, $invigilatorBusy] = $this->getBookedResources($branchId, $exam, $invigilators);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Bookings of other exams fetched']);

        $body = $this->buildRequestBody($exam, $specialty, $courses, $candidateCount, $halls, $invigilators, $hallBusy, $invigilatorBusy);

        $this->updateJobProgress($systemJob, 'PROCESSING', 'Generating Timetable', 50);
        $this->broadcastGeneration($systemJob, [
            'stage' => 'Generating Timetable....',
            'percentage' => 50,
            'details' => 'Scheduling every course with halls and invigilators.',
        ]);

        $response = app(SchedularEngine::class)->run($body);

        ExamTimetableRun::create([
            'exam_id' => $exam->id,
            'timetable_version_id' => $versionId,
            'school_branch_id' => $branchId,
            'status' => $response->status,
            'timetable_slots' => $response->timetable,
            'unscheduled_courses' => $response->unscheduled,
            'request_payload' => $this->payload,
            'scheduler_input' => $body,
            'raw_diagnostics' => [
                'hard' => $this->plain($response->diagnostics['hard']),
                'soft' => $this->plain($response->diagnostics['soft']),
            ],
            'raw_suggestions' => $response->suggestions,
        ]);

        ExamTimetableVersion::whereKey($versionId)->update(['scheduler_status' => $response->status]);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Interpreting Results....',
            'percentage' => 90,
            'details' => 'Working out solutions for anything that could not be scheduled.',
            'version' => ExamTimetableVersion::find($versionId),
        ]);

        $this->updateJobProgress($systemJob, $response->status === 'error' ? 'FAILED' : 'COMPLETED', 'Done', 100);
        $this->broadcastCompletion($systemJob, $response, $versionId);
    }

    private function getCourses(string $branchId, Exams $exam, object $specialty): Collection
    {
        $semesterId = $exam->examtype?->semester_id;

        $courses = CourseSpecialty::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialty->id)
            ->whereHas('course', fn($query) => $query
                ->where('school_branch_id', $branchId)
                ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId)))
            ->with('course')
            ->get()
            ->pluck('course')
            ->filter()
            ->unique('id');

        $active = $courses->where('status', 'active')->values();

        if ($courses->isEmpty()) {
            $this->addUserError('No Courses Created', "No courses have been created for {$specialty->specialty_name} in this exam's semester. Create and assign courses before generating the exam timetable.", [
                'specialty_id' => $specialty->id,
                'semester_id' => $semesterId,
                'reason' => 'no_courses_created',
                'path' => '/courses',
            ]);
        } elseif ($active->isEmpty()) {
            $this->addUserError('All Courses Are Deactivated', "All {$courses->count()} course(s) of {$specialty->specialty_name} in this semester are deactivated. Activate at least one course before generating the exam timetable.", [
                'specialty_id' => $specialty->id,
                'semester_id' => $semesterId,
                'deactivated_course_names' => $courses->pluck('course_title')->values()->all(),
                'reason' => 'all_courses_deactivated',
                'path' => '/courses?status=inactive',
            ]);
        }

        return $active;
    }

    private function getCandidateCount(string $branchId, Exams $exam): int
    {
        $count = ExamCandidate::where('school_branch_id', $branchId)->where('exam_id', $exam->id)->count();

        if ($count === 0) {
            $this->addUserError('No Exam Candidates', 'This exam has no candidates yet, so hall seats cannot be planned. Candidates are created for upcoming exams every night, or you can add them now.', [
                'exam_id' => $exam->id,
                'reason' => 'no_candidates',
                'path' => '/exam-candidates',
            ]);
        }

        return $count;
    }

    private function getHalls(string $branchId): Collection
    {
        $halls = Hall::where('school_branch_id', $branchId)
            ->where('status', 'available')
            ->whereHas('types', fn($query) => $query->where('key', 'exam.hall'))
            ->get();

        if ($halls->isEmpty()) {
            $this->addUserError('No Exam Halls', 'No available hall is marked as an exam hall. Mark at least one hall as an exam hall before generating the exam timetable.', [
                'branch_id' => $branchId,
                'reason' => 'no_exam_halls',
                'path' => '/hall',
            ]);
        }

        return $halls;
    }

    private function getInvigilators(string $branchId, Exams $exam): Collection
    {
        $invigilators = ExamInvigilator::where('school_branch_id', $branchId)
            ->where('exam_id', $exam->id)
            ->with('invigilatable')
            ->get()
            ->filter(fn($invigilator) => $invigilator->invigilatable !== null)
            ->values();

        if ($invigilators->isEmpty()) {
            $this->addUserError('No Invigilators Assigned', 'No invigilators have been assigned to this exam. Assign invigilators before generating the exam timetable.', [
                'exam_id' => $exam->id,
                'reason' => 'no_invigilators',
                'path' => '/exam-invigilators',
            ]);
        }

        return $invigilators;
    }

    /**
     * Halls and invigilators already booked by other exams' active timetables during this exam's dates.
     * Other exams' invigilator records are matched to this exam's invigilators by the person behind them.
     */
    private function getBookedResources(string $branchId, Exams $exam, Collection $invigilators): array
    {
        $byPerson = $invigilators->keyBy(fn($invigilator) => $invigilator->actorable_type . '|' . $invigilator->actorable_id);

        $slots = ExamTimetableSlot::where('school_branch_id', $branchId)
            ->where('exam_id', '!=', $exam->id)
            ->whereBetween('date', [Carbon::parse($exam->start_date)->toDateString(), Carbon::parse($exam->end_date)->toDateString()])
            ->whereHas('version.activeTimetable')
            ->with(['examSessionHall.invigilators.invigilator', 'examSessionInvig.invigilator'])
            ->get();

        $hallBusy = [];
        $invigilatorBusy = [];

        foreach ($slots as $slot) {
            $date = Time::date($slot->date);
            $start = Time::toTime(Time::toMinutes($slot->start_time) ?? 0);
            $end = Time::toTime(Time::toMinutes($slot->end_time) ?? 0);
            $mapPerson = function ($examInvigilator) use ($byPerson) {
                return $examInvigilator
                    ? ($byPerson[$examInvigilator->actorable_type . '|' . $examInvigilator->actorable_id]->id ?? $examInvigilator->id)
                    : null;
            };

            foreach ($slot->examSessionHall as $sessionHall) {
                $hallBusy[] = [
                    'hall_id' => $sessionHall->hall_id,
                    'date' => $date,
                    'start_time' => $start,
                    'end_time' => $end,
                    'invigilators' => $sessionHall->invigilators->map(fn($row) => $mapPerson($row->invigilator))->filter()->values()->all(),
                    'candidate_groups' => [[
                        'specialty_id' => $sessionHall->specialty_id,
                        'course_id' => $slot->course_id,
                        'candidate_count' => (int) $sessionHall->candidate_count,
                    ]],
                ];
            }

            foreach ($slot->examSessionInvig as $sessionInvigilator) {
                $person = $sessionInvigilator->invigilator;
                $mine = $person ? ($byPerson[$person->actorable_type . '|' . $person->actorable_id] ?? null) : null;

                if ($mine) {
                    $invigilatorBusy[] = [
                        'invigilator_id' => $mine->id,
                        'date' => $date,
                        'start_time' => $start,
                        'end_time' => $end,
                    ];
                }
            }
        }

        return [$hallBusy, $invigilatorBusy];
    }

    private function getJointCourses(string $branchId, Exams $exam): array
    {
        return ExamJointCourseRef::where('school_branch_id', $branchId)
            ->where('exam_id', $exam->id)
            ->with(['examJointCourse.course', 'examJointCourse.examJointCourseSlot'])
            ->get()
            ->flatMap(fn($reference) => collect($reference->examJointCourse?->examJointCourseSlot ?? [])
                ->map(fn($slot) => [
                    'course_id' => $reference->examJointCourse->course_id,
                    'course_name' => $reference->examJointCourse->course?->course_title,
                    'date' => Time::date($slot->date),
                    'start_time' => Time::toTime(Time::toMinutes($slot->start_time) ?? 0),
                    'end_time' => Time::toTime(Time::toMinutes($slot->end_time) ?? 0),
                ]))
            ->filter(fn($session) => $session['course_id'] && $session['date'])
            ->values()
            ->all();
    }

    private function buildRequestBody(
        Exams $exam,
        object $specialty,
        Collection $courses,
        int $candidateCount,
        Collection $halls,
        Collection $invigilators,
        array $hallBusy,
        array $invigilatorBusy,
    ): array {
        $teacherIds = $invigilators->where('actorable_type', Teacher::class)->pluck('actorable_id')->all();
        $taught = TeacherCoursePreference::where('school_branch_id', $this->currentSchool->id)
            ->whereIn('teacher_id', $teacherIds)
            ->get()
            ->groupBy('teacher_id');

        $request = collect($this->payload)->only([...ExamPayload::HARD, ...ExamPayload::SOFT])->all();
        $payload = [
            'start_date' => Carbon::parse($exam->start_date)->toDateString(),
            'end_date' => Carbon::parse($exam->end_date)->toDateString(),
            'specialty_id' => $specialty->id,
            'candidate_count' => $candidateCount,
            'courses' => $courses->map(fn($course) => [
                'course_id' => $course->id,
                'course_name' => $course->course_title,
                'course_code' => $course->course_code ?? null,
            ])->values()->all(),
            'halls' => $halls->map(fn($hall) => [
                'hall_id' => $hall->id,
                'hall_name' => $hall->name,
                'capacity' => (int) $hall->capacity,
            ])->values()->all(),
            'invigilators' => $invigilators->map(fn($invigilator) => [
                'invigilator_id' => $invigilator->id,
                'name' => $invigilator->invigilatable->name ?? null,
                'actor_type' => $invigilator->actorable_type,
                'actor_id' => $invigilator->actorable_id,
                'course_taught' => $invigilator->actorable_type === Teacher::class
                    ? ($taught[$invigilator->actorable_id] ?? collect())->pluck('course_id')->values()->all()
                    : [],
            ])->values()->all(),
            'hall_busy_slots' => $hallBusy,
            'invigilator_busy_slots' => $invigilatorBusy,
            'hard_constraints' => [],
            'soft_constraints' => [],
        ];

        $payload = ExamPayload::withRequest($payload, $request);
        $payload['hard_constraints'][RequiredJointCourseSession::KEY] = $this->getJointCourses($this->currentSchool->id, $exam);

        return $payload;
    }

    private function plain(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?? [];
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
        Log::error("GenerateFixedExamTimetable job [{$this->jobId}] permanently failed.", [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
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

    private function addUserError(string $title, string $description, array $params = []): void
    {
        $this->userErrors->push([
            'title' => $title,
            'description' => $description,
            'additional_params' => $params,
        ]);
    }

    private function createUserError(string $versionId, string $examId): void
    {
        ExamTimetableError::create([
            'id' => Str::uuid()->toString(),
            'exam_id' => $examId,
            'timetable_version_id' => $versionId,
            'school_branch_id' => $this->currentSchool->id,
            'status' => 'failed',
            'request_payload' => $this->payload,
            'errors' => $this->userErrors->toArray(),
        ]);

        ExamTimetableVersion::whereKey($versionId)->update(['scheduler_status' => ExamTimetableVersion::STATUS_FAILED]);
    }
}
