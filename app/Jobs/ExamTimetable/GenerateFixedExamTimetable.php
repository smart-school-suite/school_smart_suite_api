<?php

namespace App\Jobs\ExamTimetable;

use App\Events\ExamTimetable\ExamTimetableGenerationEvent;
use App\Exceptions\AppException;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\Exams;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Models\Hall;
use App\Models\Job\SystemJob;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\TeacherCoursePreference;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AccessedStudent as ExamCandidate;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use Illuminate\Support\Facades\Log;
use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class GenerateFixedExamTimetable implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 30;
    public function __construct(
        protected readonly object $currentSchool,
        protected readonly array $payload,
        protected readonly string $jobId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $systemJob = SystemJob::with('initiatedBy')->find($this->jobId);
        if (!$systemJob) {
            Log::warning("GenerateFixedSemesterTimetable: SystemJob [{$this->jobId}] not found. Aborting.");
            return;
        }

        $exam = Exams::where("school_branch_id", $this->currentSchool->id)
            ->with(['specialty.level'])
            ->where('id', $this->payload['exam_id'])
            ->firstOrFail();

        try {
            $this->process($systemJob, $exam);
        } catch (AppException $e) {
            $this->failJob($systemJob, $e->getMessage(), $e->getCode());
            $this->fail($e);
        } catch (Throwable $e) {
            $this->failJob($systemJob, $e->getMessage(), 500);
            $this->fail($e);
        }
    }

    private function process(SystemJob $systemJob, Exams $exam): void
    {
        $this->updateJobProgress($systemJob, 'PROCESSING', 'Gathering Data', 10);

        $branchId    = $this->currentSchool->id;
        $timetableVersionId = $this->payload['version_id'] ?? null;
        $requestPayload = $this->payload;

        ExamTimetableGenerationEvent::dispatch(
            $systemJob->initiatedBy,
            $this->currentSchool,
            [
                'stage' => 'Gathering Data....',
                'percentage' => 10,
                'details' => 'Fetching invigilators, courses, halls, and constraints to prepare for timetable generation.',
            ]
        );

        $invigilators = $this->getExamInvigilator($branchId, $exam->id);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Invigilators Fetched']);

        $courses     = $this->getCourses($branchId, $exam);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam Courses Fetched']);

        $halls              = $this->getHalls($branchId);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Exam Halls Fetched']);

        $hallBusySlots    = $this->getHallBusySlots($branchId);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Hall busy periods fetched']);

        $invigBusySlots = $this->getInvigilatorBusySlot($branchId);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Inviglator Busy Periods Fetched']);

        $jointCourses       = $this->getJointCourse($branchId, $exam);

        $candidateCount    = (int) $this->getCandidateCount($exam->id, $branchId);

        $body = $this->buildRequestBody(
            $invigilators,
            $courses,
            $halls,
            $hallBusySlots,
            $invigBusySlots,
            $jointCourses,
            $candidateCount,
            $requestPayload
        );

        $this->updateJobProgress($systemJob, 'PROCESSING', 'Generating Timetable', 50);

        ExamTimetableGenerationEvent::dispatch(
            $systemJob->initiatedBy,
            $this->currentSchool,
            [
                'stage' => 'Generating Timetable....',
                'percentage' => 50,
                'details' => 'Generating the optimal timetable based on gathered data and constraints.',
            ]
        );

        $schedulingEngine = app(SchedularEngine::class);
        $response = $schedulingEngine->run($body);

        if (is_null($timetableVersionId)) {
            $timetableVersionId = $this->createTimetableVersion(
                $this->payload['exam_id'],
                $this->currentSchool,
                $response
            );
        }

        if (!$this->isErrorResponse($response)) {
            $this->createTimetableSlots(
                $timetableVersionId,
                $this->currentSchool,
                $response,
                $exam
            );
        }

        $finalStatus = $this->isErrorResponse($response) ? 'FAILED' : 'COMPLETED';

        ExamTimetableGenerationEvent::dispatch(
            $systemJob->initiatedBy,
            $this->currentSchool,
            [
                'stage' => 'Interpreting Results....',
                'percentage' => 90,
                'details' => 'Interpreting the results from the Exam Timetable generation process.',
                'version' => ExamTimetableVersion::find($timetableVersionId) ?? null,
            ]
        );

        // $this->handleDiagnostics($response, $timetableVersionId, $schoolSemester);
        $this->updateJobProgress($systemJob, $finalStatus, 'Done', 100);
    }
    private function buildRequestBody(
        collection $invigilators,
        collection $courses,
        collection  $halls,
        collection  $hallBusySlots,
        collection  $invigBusySlots,
        collection  $jointCourses,
        int $candidateCount,
        array  $requestPayload
    ) {
        return array_filter([
            "start_date" => $exam->start_date ?? null,
            "end_date" => $exam->end_date ?? null,
            "candidate_count" => $candidateCount,
            "halls" => $this->formatHall($halls),
            "courses" => $this->formatCourse($courses),
            "hall_busy_slots" => $this->formatInvigBusySlot($hallBusySlots),
            "invigilator_busy_slots" => $this->formatHallBusySlot($invigBusySlots),
            "invigilators" => $invigilators,
            'soft_constraints'     => $this->buildSoftConstraints($requestPayload),
            'hard_constraints'     => $this->buildHardConstraints($requestPayload, $jointCourses),
        ]);
    }

    private function buildSoftConstraints(array $requestPayload): array
    {
        return array_filter([
            'requested_assignments'            => $requestPayload['requested_assignments'] ?? null,
            'student_daily_load'      => $requestPayload['student_daily_load'] ?? null,
            'invigilator_requested_slot' => $requestPayload['invigilator_requested_slot'] ?? null,
            'course_requested_slot' => $requestPayload['course_requested_slot'] ?? null,
        ], fn($value) => !is_null($value));
    }
    private function buildHardConstraints(array $requestPayload, ?collection $jointCourses): array
    {
        return array_filter([
            'required_joint_course_periods'    => $this->formatJointCourse($jointCourses),
            'session_duration'              => $requestPayload['session_duration'] ?? null,
            'operational_period'   => $requestPayload['operational_period'] ?? null,
        ], fn($value) => !is_null($value));
    }
    private function formatHall(Collection $halls)
    {
        return $halls->map(fn($hall) => [
            "hall_id" => $hall->id,
            "capacity" => $hall->name,
            "hall_name" => $hall->name
        ]);
    }
    private function formatCourse(Collection $courses)
    {
        return $courses->map(fn($course) => [
            "course_id" => $course->id,
            "course_name" => $course->title
        ]);
    }
    private function formatInvigBusySlot(Collection $invigBusySlots)
    {
        return $invigBusySlots->groupBy(function ($slot) {
            return $slot->examSessionInvig->invigilator->invigilatable->id;
        })->flatMap(function ($slots, $invigilatorId) {
            return $slots->groupBy(function ($slot) {
                return $slot->exam->date;
            })->map(function ($dateSlots, $date) use ($invigilatorId) {
                return [
                    'invigilator_id' => $invigilatorId,
                    'date' => $date,
                    'slots' => $dateSlots->map(function ($slot) {
                        return [
                            'hall_id' => $slot->hall_id,
                            'course_id' => $slot->course_id,
                            'specialty_id' => $slot->specialty_id,
                            'start_time' => date('H:i', strtotime($slot->start_time)),
                            'end_time' => date('H:i', strtotime($slot->end_time))
                        ];
                    })->values()->toArray()
                ];
            })->values();
        })->values();
    }
    private function formatHallBusySlot(Collection $hallBusySlots)
    {
        return $hallBusySlots->groupBy(function ($slot) {
            return $slot->examSessionHall->hall->id;
        })->flatMap(function ($slots, $hallId) {
            return $slots->groupBy(function ($slot) {
                return $slot->exam->date;
            })->map(function ($dateSlots, $date) use ($hallId) {
                return [
                    'hall_id' => $hallId,
                    'date' => $date,
                    'slots' => $dateSlots->map(function ($slot) {
                        return [
                            'start_time' => date('H:i', strtotime($slot->start_time)),
                            'end_time' => date('H:i', strtotime($slot->end_time)),
                            'invigilators' => $slot->examSessionHall->invigilators->map(function ($invigilator) {
                                return $invigilator->invigilator->invigilatable->id;
                            })->values()->toArray(),
                            'candidate_groups' => $slot->candidateGroups->map(function ($candidateGroup) {
                                return [
                                    'specialty_id' => $candidateGroup->specialty_id,
                                    'course_id' => $candidateGroup->course_id,
                                    'candidate_count' => $candidateGroup->candidate_count
                                ];
                            })->values()->toArray()
                        ];
                    })->values()->toArray()
                ];
            })->values();
        })->values();
    }
    private function getHallBusySlots(string $schoolBranchId)
    {
        $hallBusySlots = ExamTimetableSlot::where("school_branch_id", $schoolBranchId)
            ->with([
                'examSessionHall.hall',
                'examSessionHall.invigilators.invigilator.invigilatable',
                'candidateGroups',
                'exam'
            ])
            ->whereHas('exam', function ($query) {
                $query->where("date", ">=", now()->toDateString());
            })
            ->whereHas('examSessionHall.version', function ($query) {
                $query->whereHas('activeTimetable');
            })
            ->get();

        return $this->formatHallBusySlot($hallBusySlots);
    }
    private function getInvigilatorBusySlot(string $schoolBranchId)
    {
        $invigBusySlots = ExamTimetableSlot::where("school_branch_id", $schoolBranchId)
            ->with(['examSessionInvig.invigilator.invigilatable', 'exam'])
            ->whereHas('exam', function ($query) {
                $query->where("date", ">=", now()->toDateString());
            })
            ->whereHas('examSessionInvig.version', function ($query) {
                $query->whereHas('activeTimetable');
            })
            ->get();

        return $this->formatInvigBusySlot($invigBusySlots);
    }
    private function formatJointCourse(Collection $jointCourse)
    {
        return $jointCourse->flatMap(function ($jointCourseRef) {
            return $jointCourseRef->examJointCourse->examJointCourseSlot->map(function ($slot) use ($jointCourseRef) {
                return [
                    'course_id' => $jointCourseRef->examJointCourse->course_id,
                    'date' => $slot->date,
                    'start_time' => date('H:i', strtotime($slot->start_time)),
                    'end_time' => date('H:i', strtotime($slot->end_time))
                ];
            });
        })->values();
    }
    private function getJointCourse(string $schoolBranchId, Exams $exam)
    {
        return ExamJointCourseRef::where("school_branch_id", $schoolBranchId)
            ->where("exam_id", $exam->id)
            ->with(['examJointCourse.examJointCourseSlot'])
            ->get();
    }
    private function getCourses(string $schoolBranchId, Exams $exam)
    {
        return  SemesterTimetableSlot::where("school_branch_id", $schoolBranchId)
            ->where("school_semester_id", $exam->school_semester_id)
            ->with(['course'])
            ->get()
            ->pluck('course');
    }
    private function getExamInvigilator(string $schoolBranchId, string $examId)
    {
        $examInvigs = ExamInvigilator::where("school_branch_id", $schoolBranchId)
            ->where("exam_id", $examId)
            ->with(['invigilator.invigilatable'])
            ->get();

        $invigilatorCourses = TeacherCoursePreference::where("school_branch_id", $schoolBranchId)
            ->whereIn("teacher_id", $examInvigs->pluck('invigilator.invigilatable.id')->toArray())
            ->get();

        $formattedInvigilators = $examInvigs->map(function ($examInvig) use ($invigilatorCourses) {
            $invigilator = $examInvig->invigilator;
            $invigilatable = $invigilator->invigilatable;

            $coursesTaught = $invigilatorCourses
                ->where('teacher_id', $invigilatable->id)
                ->pluck('course_id')
                ->values()
                ->toArray();

            return [
                'invigilator_id' => $invigilatable->id,
                'name' => $invigilatable->name,
                'course_taught' => $coursesTaught
            ];
        })->values();

        return $formattedInvigilators;
    }
    private function getCandidateCount(string $examId, string $schoolBranchId)
    {
        return ExamCandidate::where("school_branch_id", $schoolBranchId)
            ->where("exam_id", $examId)
            ->count();
    }
    private function getHalls(string $schoolBranchId)
    {
        return Hall::where("school_branch_id", $schoolBranchId)
            ->with(['types', function ($query) {
                $query->where("key", "exam.hall");
            }])->get();
    }
    private function failJob(SystemJob $systemJob, string $message, int|string $code): void
    {
        $systemJob->update([
            'status'        => 'FAILED',
            'stage'         => 'Failed',
            'progress'      => 0,
            'error_code'    => (string) $code,
            'error_message' => $message,
            'updated_at'    => Carbon::now(),
        ]);

        $systemJob->systemJobEvent()->create([
            'event_type' => 'error',
            'message'    => $message,
        ]);
    }
    private function failed(Throwable $exception): void
    {
        Log::error("GenerateFixedSemesterTimetable job [{$this->jobId}] permanently failed.", [
            'exception' => $exception->getMessage(),
            'school_id' => $this->currentSchool->id,
            'payload'   => $this->payload,
        ]);

        $systemJob = SystemJob::find($this->jobId);

        if ($systemJob) {
            $this->failJob($systemJob, $exception->getMessage(), 500);
        }
    }
    private function isErrorResponse(object $response): bool
    {
        return Str::lower($response->status ?? 'error') === 'error';
    }
    private function updateJobProgress(SystemJob $systemJob, string $status, string $stage, int $progress): void
    {
        $systemJob->update([
            'status'     => $status,
            'stage'      => $stage,
            'progress'   => $progress,
            'updated_at' => Carbon::now(),
        ]);
    }
    private function createTimetableVersion(
        string $schoolSemesterId,
        object $currentSchool,
        object $response,
    ): string {
        $nextVersion = (ExamTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $schoolSemesterId)
            ->max('version_number') ?? 0) + 1;

        $version = ExamTimetableVersion::create([
            'label'            => "Version {$nextVersion}",
            'scheduler_status' => $response->status ?? 'error',
            'school_branch_id' => $currentSchool->id,
            'school_semester_id'      => $schoolSemesterId,
            'version_number'   => $nextVersion
        ]);

        return $version->id;
    }

    private function createTimetableSlots(
        string $versionId,
        object $currentSchool,
        object $response,
        object $exam
    ) {
        $specialtyId = $exam->schoolSemester->specialty_id;
        $slots = collect($response->timetable);
    }
}
