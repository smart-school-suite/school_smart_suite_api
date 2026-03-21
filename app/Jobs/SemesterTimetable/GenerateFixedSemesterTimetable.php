<?php

namespace App\Jobs\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\Course\CourseSpecialty;
use App\Models\Course\JointCourseSlot;
use App\Models\Course\SemesterJoinCourseReference;
use App\Models\Course\SemesterJointCourse;
use App\Models\Job\SystemJob;
use App\Models\SchoolSemester;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Models\SpecialtyHall;
use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use App\Services\SemesterTimetableScheduler\FixedSchedulingClient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Courses;
use App\Models\Hall;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Teacher;
use Throwable;

class GenerateFixedSemesterTimetable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 30;

    public function __construct(
        protected readonly object $currentSchool,
        protected readonly array $payload,
        protected readonly string $jobId,
        protected readonly FixedSchedulingClient $schedulingClient,
    ) {}

    public function handle(): void
    {
        $systemJob = SystemJob::with('initiatedBy')->find($this->jobId);

        if (!$systemJob) {
            Log::warning("GenerateFixedSemesterTimetable: SystemJob [{$this->jobId}] not found. Aborting.");
            return;
        }

        $schoolSemester = SchoolSemester::where('school_branch_id', $this->currentSchool->id)
            ->with(['semester', 'specialty.level'])
            ->where('id', $this->payload['school_semester_id'])
            ->firstOrFail();

        try {
            $this->process($systemJob, $schoolSemester);
        } catch (AppException $e) {
            $this->failJob($systemJob, $e->getMessage(), $e->getCode());
            $this->fail($e);
        } catch (Throwable $e) {
            $this->failJob($systemJob, $e->getMessage(), 500);
            $this->fail($e);
        }
    }

    private function process(SystemJob $systemJob, SchoolSemester $schoolSemester): void
    {
        $this->updateJobProgress($systemJob, 'PROCESSING', 'Gathering Data', 10);

        $branchId    = $this->currentSchool->id;
        $semesterId  = $schoolSemester->id;
        $specialty = $schoolSemester->specialty;
        $requestPayload = $this->payload['request_payload'];

        $teachers           = $this->getTeachers($branchId, $specialty);
        $teacherIds         = $teachers->pluck('teacher_id')->toArray();
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Teachers fetched']);

        $teacherCourses     = $this->getTeacherCourses($branchId, $teacherIds, $schoolSemester);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Teacher courses fetched']);

        $halls              = $this->getHalls($branchId, $specialty);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Halls fetched']);

        $hallBusyPeriods    = $this->getHallBusyPeriods($branchId, $halls);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Hall busy periods fetched']);

        $teacherBusyPeriods = $this->getTeacherBusyPeriods($branchId, $teacherIds);
        $systemJob->systemJobEvent()->create(['event_type' => 'info', 'message' => 'Teacher busy periods fetched']);

        $jointCourses       = $this->getJointCourses($schoolSemester);

        $body = $this->buildRequestBody(
            $teachers,
            $teacherBusyPeriods,
            $teacherCourses,
            $halls,
            $hallBusyPeriods,
            $requestPayload,
            $jointCourses,
        );

        $this->updateJobProgress($systemJob, 'PROCESSING', 'Generating Timetable', 50);

        $response = $this->optimalSchedulerResponseMock();

        $timetableVersionId = $this->createTimetableVersion(
            $this->payload['school_semester_id'],
            $this->currentSchool,
            $response,
            $requestPayload,
        );

        if (!$this->isErrorResponse($response)) {
            $this->createTimetableSlots($timetableVersionId, $this->currentSchool, $response, $schoolSemester);
        }

        $finalStatus = $this->isErrorResponse($response) ? 'FAILED' : 'COMPLETED';
        $this->updateJobProgress($systemJob, $finalStatus, 'Done', 100);
    }
    private function getTeachers(string $branchId, $specialty)
    {
        $teachers = TeacherSpecailtyPreference::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialty->id)
            ->with(['teacher' => fn($q) => $q->where('status', 'active')])
            ->get();

        if ($teachers->isEmpty()) {
            throw new AppException(
                "No Teachers Found",
                404,
                "No Teachers Found",
                "No teachers are assigned to {$specialty->specialty_name} {$specialty->level->name}. Please assign teachers before generating a timetable.",
            );
        }

        return $teachers;
    }

    private function getTeacherCourses(string $branchId, array $teacherIds, SchoolSemester $schoolSemester): Collection
    {
        $courseIds = CourseSpecialty::where('school_branch_id', $branchId)
            ->where('specialty_id', $schoolSemester->specialty_id)
            ->whereHas(
                'course',
                fn($q) => $q
                    ->where('school_branch_id', $branchId)
                    ->where('semester_id', $schoolSemester->semester_id)
                    ->where('status', 'active')
            )
            ->whereDoesntHave(
                'course.courseSpecialty',
                fn($q) => $q
                    ->where('school_branch_id', $branchId)
                    ->whereColumn('course_id', 'course_specialties.course_id')
                    ->where('specialty_id', '!=', $schoolSemester->specialty_id)
            )
            ->pluck('course_id')
            ->toArray();

        if (empty($courseIds)) {
            throw new AppException(
                "No Courses Found",
                404,
                "No Courses Found",
                "No active non-joint courses were found for this specialty and semester.",
            );
        }

        $teacherCourses = TeacherCoursePreference::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->whereIn('course_id', $courseIds)
            ->with(['course.courseSpecialty', 'teacher'])
            ->get();

        if ($teacherCourses->isEmpty()) {
            throw new AppException(
                "No Teacher-Course Assignments Found",
                404,
                "No Teacher-Course Assignments Found",
                "None of the assigned teachers have been matched to any course in this specialty and semester. Please ensure teacher-course preferences are configured.",
            );
        }

        return $teacherCourses;
    }

    private function getHalls(string $branchId, $specialty)
    {
        $halls = SpecialtyHall::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialty->id)
            ->with('hall.types')
            ->get();

        if ($halls->isEmpty()) {
            throw new AppException(
                "No Halls Found",
                404,
                "No Halls Found For This Specialty",
                "No halls are assigned to {$specialty->specialty_name} {$specialty->level->name}. Please assign halls before generating a timetable.",
            );
        }

        return $halls;
    }

    private function getHallBusyPeriods(string $branchId, $halls)
    {
        return SemesterTimetableSlot::where('school_branch_id', $branchId)
            ->whereIn('hall_id', $halls->pluck('hall_id')->toArray())
            ->whereHas('schoolSemester', fn($q) => $q->where('end_date', '>=', now()))
            ->with('hall')
            ->get();
    }

    private function getTeacherBusyPeriods(string $branchId, array $teacherIds)
    {
        return SemesterTimetableSlot::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->whereHas('schoolSemester', fn($q) => $q->where('end_date', '>=', now()))
            ->with('teacher')
            ->get();
    }

    private function getJointCourses(SchoolSemester $schoolSemester): ?array
    {
        $branchId   = $this->currentSchool->id;
        $semesterId = $schoolSemester->id;

        $existingJointCourses = SemesterJointCourse::where('school_branch_id', $branchId)
            ->where('semester_id', $semesterId)
            ->exists();

        if (!$existingJointCourses) {
            return null;
        }

        $reference = SemesterJoinCourseReference::where('school_branch_id', $branchId)
            ->where('school_semester_id', $semesterId)
            ->first();

        if (!$reference) {
            return null;
        }

        $jointCourseSlots = JointCourseSlot::where('school_branch_id', $branchId)
            ->where('semester_joint_course_id', $reference->semester_joint_course_id)
            ->with(['course', 'teacher', 'hall'])
            ->get();

        if ($jointCourseSlots->isEmpty()) {
            throw new AppException(
                "No Joint Course Slots Found",
                404,
                "No Joint Course Slots Found",
                "Joint courses exist for this semester but no slots have been created. Please create slots for all joint courses before generating the timetable.",
            );
        }

        return $jointCourseSlots
            ->groupBy(fn($slot) => $slot->course->id . '|' . $slot->teacher->id)
            ->map(fn($slots) => [
                'course_id'  => $slots->first()->course->id,
                'teacher_id' => $slots->first()->teacher->id,
                'periods'    => $slots->map(fn($slot) => [
                    'day'        => $slot->day_of_week,
                    'start_time' => $slot->start_time,
                    'end_time'   => $slot->end_time,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }
    private function buildRequestBody(
        $teachers,
        $teacherBusyPeriods,
        $teacherCourses,
        $halls,
        $hallBusyPeriods,
        array $requestPayload,
        $jointCourses,
    ): array {
        return [
            'teachers'             => $this->formatTeachers($teachers),
            'teacher_busy_period'  => $this->formatTeacherBusyPeriods($teacherBusyPeriods),
            'teacher_courses'      => $this->formatTeacherCourses($teacherCourses),
            'halls'                => $this->formatHalls($halls),
            'hall_busy_periods'    => $this->formatHallBusyPeriods($hallBusyPeriods),
            'soft_constraints'     => $this->buildSoftConstraints($requestPayload),
            'hard_constraints'     => $this->buildHardConstraints($requestPayload, $jointCourses),
        ];
    }

    private function formatTeachers($teachers): array
    {
        return $teachers->map(fn($t) => [
            'teacher_id' => $t->teacher->id,
            'name'       => $t->teacher->name,
        ])->all();
    }

    private function formatTeacherBusyPeriods($busyPeriods): array
    {
        return $busyPeriods->map(fn($s) => [
            'start_time'   => $s->start_time,
            'end_time'     => $s->end_time,
            'day'          => $s->day,
            'teacher_id'   => $s->teacher_id,
            'teacher_name' => $s->teacher->name,
        ])->all();
    }

    private function formatTeacherCourses($teacherCourses): array
    {
        return $teacherCourses->map(fn($c) => [
            'course_id'     => $c->course->id,
            'course_title'  => $c->course->course_title,
            'course_credit' => $c->course->credit,
            'course_type'   => 'theoretical',
            'teacher_id'    => $c->teacher->id,
            'teacher_name'  => $c->teacher->name,
        ])->all();
    }

    private function formatHalls($halls): array
    {
        return $halls->map(fn($h) => [
            'hall_name'     => $h->hall->name,
            'hall_id'       => $h->hall->id,
            'hall_capacity' => $h->hall->capacity,
            'hall_type'     => $h->hall->types->pluck('name')->all(),
        ])->all();
    }

    private function formatHallBusyPeriods($busyPeriods): array
    {
        return $busyPeriods->map(fn($s) => [
            'hall_id'    => $s->hall->id,
            'hall_name'  => $s->hall->name,
            'start_time' => $s->start_time,
            'end_time'   => $s->end_time,
            'day'        => $s->day,
        ])->all();
    }

    private function buildSoftConstraints(array $requestPayload): array
    {
        return array_filter([
            'course_daily_frequency'      => $requestPayload['course_daily_frequency'] ?? null,
            'course_requested_time_slots' => $requestPayload['course_requested_time_slots'] ?? null,
            'hall_requested_time_windows' => $requestPayload['hall_requested_time_windows'] ?? null,
            'requested_assignments'            => $requestPayload['requested_assignments'] ?? null,
            'teacher_daily_hours'              => $requestPayload['teacher_daily_hours'] ?? null,
            'teacher_requested_time_windows'   => $requestPayload['teacher_requested_time_windows'] ?? null,
            'teacher_weekly_hours'             => $requestPayload['teacher_weekly_hours'] ?? null,
            'schedule_periods_per_day'         => $requestPayload['schedule_periods_per_day'] ?? null,
            'schedule_free_periods_per_day'    => $requestPayload['schedule_free_periods_per_day'] ?? null,
            'requested_free_periods'           => $requestPayload['requested_free_periods'] ?? null,
        ], fn($value) => !is_null($value));
    }

    private function buildHardConstraints(array $requestPayload, ?array $jointCourses): array
    {
        return array_filter([
            'required_joint_course_periods'    => $jointCourses,
            'break_period'                     => $requestPayload['break_period'] ?? null,
            'operational_period'               => $requestPayload['operational_period'] ?? null,
            'schedule_period_duration_minutes' => $requestPayload['schedule_period_duration_minutes'] ?? null,
        ], fn($value) => !is_null($value));
    }

    private function createTimetableVersion(
        string $schoolSemesterId,
        object $currentSchool,
        array $response,
        array $requestPayload,
    ): string {
        $nextVersion = (SemesterTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $schoolSemesterId)
            ->max('version_number') ?? 0) + 1;

        $version = SemesterTimetableVersion::create([
            'label'            => "Version {$nextVersion}",
            'scheduler_status' => $response['status'] ?? 'error',
            'school_branch_id' => $currentSchool->id,
            'school_semester_id'      => $schoolSemesterId,
            'version_number'   => $nextVersion,
            'scheduler_input'  => $requestPayload,
            'scheduler_output' => $response,
        ]);

        return $version->id;
    }

    private function createTimetableSlots(string $versionId, object $currentSchool, array $response, $schoolSemester): void
    {
        $now = Carbon::now();

        $slots = collect($response['timetable'])
            ->flatMap(fn($day) => $day['slots'])
            ->map(fn($slot) => [
                'id'               => Str::uuid()->toString(),
                'school_branch_id' => $currentSchool->id,
                'school_semester_id' => $schoolSemester->id,
                'student_batch_id' => $schoolSemester->student_batch_id,
                'specialty_id'    => $schoolSemester->specialty_id,
                'version_id'       => $versionId,
                'course_id'        => Arr::random(Courses::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray()) ?? null,
                'teacher_id'       => Arr::random(Teacher::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray()) ?? null,
                'hall_id'          => Arr::random(Hall::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray()) ?? null,
                'day'              => $slot['day'],
                'start_time'       => $slot['start_time'],
                'end_time'         => $slot['end_time'],
                'break_period'     => $slot['break'] ?? false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ])
            ->all();

        foreach (array_chunk($slots, 500) as $chunk) {
            DB::table('timetable_slots')->insert($chunk);
        }
    }

    private function isErrorResponse(array $response): bool
    {
        return Str::lower($response['status'] ?? 'error') === 'error';
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

    public function failed(Throwable $exception): void
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

    private static function optimalSchedulerResponseMock()
    {
        $filePath = public_path("schedulerResponse/optimal/example1.json");
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        return $data;
    }
}
