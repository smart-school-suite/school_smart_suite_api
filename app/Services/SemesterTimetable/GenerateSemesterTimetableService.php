<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\InstructorAvailabilitySlot;
use App\Models\SchoolSemester;
use App\Models\SemesterTimetable\SemesterTimetableDraft;
use App\Models\SemesterTimetable\SemesterTimetablePrompt;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Models\SpecialtyHall;
use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use App\Models\Timetable;
use App\Services\SemesterTimetableAI\GeminiIntentService;
use App\Services\SemesterTimetableAI\GeminiJsonService;
use App\Services\SemesterTimetableScheduler\PreferenceSchedulingClient;
use Carbon\Carbon;

class GenerateSemesterTimetableService
{
    protected PreferenceSchedulingClient $schedulingClient;
    public function __construct(
        protected GeminiIntentService $geminiIntentService,
        protected GeminiJsonService $geminiJsonService,
        PreferenceSchedulingClient $schedulingClient,
    ) {
        $this->schedulingClient = $schedulingClient;
    }

    public function generateTimetable(array $data, $currentSchool): array
    {
        $stringent = $this->geminiIntentService->classify($data['prompt']);

        if ($stringent['is_unrelated']) {
            return [
                'is_unrelated' => true,
                'message' => "I’m here to help specifically with semester timetables. You can ask me to create a timetable, adjust class schedules, or add scheduling constraints to fit your academic needs.",
            ];
        }

        $semester = $this->getSchoolSemester($data['school_semester_id']);
        $teachers = $this->getTeachers($currentSchool->id, $semester->specialty_id);
        $teacherIds = $teachers->pluck('teacher_id')->toArray();

        $preferred = $this->getTeacherPreferredSchedule($currentSchool->id, $semester->id, $semester->specialty_id, $teacherIds);
        if ($preferred->isEmpty()) {
            throw new AppException(
                "Teacher Prefered Teaching Slot Not Added",
                404,
                "Teacher Preferred Teaching Period Not Added",
                "Teacher Preferred Teaching for {$semester->semester->name} {$semester->specialty->specialty_name}, {$semester->specialty->level->level} please ensure that all teachers have added their preferred teaching times"
            );
        }

        $teacherCourses = $this->getTeacherCourses($currentSchool->id, $teacherIds, $semester);
        if ($teacherCourses->isEmpty()) {
            throw new AppException(
                "No Courses Assigned to teacher",
                404,
                "No Courses Assigned to teacher",
                "No Courses Assigned to this teachers found for {$semester->semester->name} {$semester->specialty->specialty_name}, {$semester->specialty->level->level}"
            );
        }

        $halls = $this->getHalls($currentSchool->id, $semester->specialty_id);

        $hallBusy = $this->getHallBusyPeriods($currentSchool->id, $halls);
        $teacherBusy = $this->getTeacherBusyPeriods($currentSchool->id, $teacherIds);

        $promptResponse = $this->geminiJsonService->generateStructuredJson(
            $data['prompt'],
            $this->buildPromptPayload($teacherCourses, $teachers, $halls)
        );

        if ($data['draft_id'] ?? null === null) {
            $draft = $this->createTimetableDraft($data, $currentSchool);
            $draftId = $draft->id;
            $this->createTimetableVersion($data, $currentSchool, $draftId, 'processing');
        }
        return $this->buildBody(
            $preferred,
            $teachers,
            $teacherBusy,
            $teacherCourses,
            $halls,
            $hallBusy,
            $promptResponse
        );
    }
    private function getSchoolSemester(string $id): SchoolSemester
    {
        return SchoolSemester::with(['specialty.level', 'semester'])->findOrFail($id);
    }

    private function getTeachers(string $branchId, string $specialtyId)
    {
        $q = TeacherSpecailtyPreference::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialtyId)
            ->with('teacher')
            ->get();

        if ($q->isEmpty()) {
            throw new AppException(
                "No Teachers Found",
                404,
                "No Teachers Found",
                "No Teachers Found for specialty {$specialtyId} — please make sure teachers have been assigned to this specialty before creating the timetable"
            );
        }

        return $q;
    }

    private function getTeacherPreferredSchedule(string $branchId, string $semesterId, string $specialtyId, array $teacherIds)
    {
        return InstructorAvailabilitySlot::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialtyId)
            ->where('school_semester_id', $semesterId)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->get();
    }

    private function getTeacherCourses(string $branchId, array $teacherIds, SchoolSemester $semester)
    {
        return TeacherCoursePreference::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->whereHas('course', fn($q) => $q->where('semester_id', $semester->semester_id)
                ->where('specialty_id', $semester->specialty_id))
            ->with(['course.types', 'teacher'])
            ->get();
    }

    private function getHalls(string $branchId, string $specialtyId)
    {
        $halls = SpecialtyHall::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialtyId)
            ->with('hall.types')
            ->get();

        if ($halls->isEmpty()) {
            throw new AppException(
                "No Halls Assigned to this specialty",
                404,
                "No Halls Found For this specialty",
                "No Halls Found for specialty {$specialtyId} — please ensure that halls have been assigned to this specialty before creating timetable"
            );
        }

        return $halls;
    }

    private function getHallBusyPeriods(string $branchId, $halls)
    {
        $hallIds = $halls->pluck('hall_id')->toArray();
        return Timetable::where('school_branch_id', $branchId)
            ->whereIn('hall_id', $hallIds)
            ->with('hall')
            ->get();
    }

    private function getTeacherBusyPeriods(string $branchId, array $teacherIds)
    {
        return Timetable::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->get();
    }

    private function buildPromptPayload($teacherCourses, $teachers, $halls): array
    {
        return [
            'courses' => $teacherCourses->map(fn($c) => [
                'course_id' => $c->course->id,
                'course_title' => $c->course->course_title,
                'course_type' => $c->course->types,
                'credit' => $c->course->credit,
                'course_code' => $c->course->course_code,
            ]),
            'teachers' => $teachers->map(fn($t) => [
                'teacher_id' => $t->teacher->id,
                'teacher_name' => $t->teacher->name,
            ]),
            'halls' => $halls->map(fn($h) => [
                'hall_id' => $h->hall->id,
                'hall_name' => $h->hall->name,
                'capacity' => $h->hall->capacity,
                'type' => $h->hall->types,
            ]),
        ];
    }

    private function buildBody($preferred, $teachers, $teacherBusy, $teacherCourses, $halls, $hallBusy, $promptResponse): array
    {
        return [
            'teacher_prefered_teaching_period' => $preferred->map(fn($s) => [
                'start_time' => Carbon::createFromFormat('H:i:s', $s->start_time)->format('H:i'),
                'end_time' => Carbon::createFromFormat('H:i:s', $s->end_time)->format('H:i'),
                'day' => $s->day_of_week,
                'teacher_id' => $s->teacher_id,
                'teacher_name' => $s->teacher->name,
            ]),
            'teachers' => $teachers->map(fn($t) => [
                'teacher_id' => $t->teacher->id,
                'name' => $t->teacher->name,
            ]),
            'teacher_busy_period' => $teacherBusy->map(fn($s) => [
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'day' => $s->day_of_week,
                'teacher_id' => $s->teacher_id,
                'teacher_name' => $s->teacher->name,
            ]),
            'teacher_courses' => $teacherCourses->map(fn($c) => [
                'course_id' => $c->course->id,
                'course_title' => $c->course->course_title,
                'course_credit' => $c->course->credit,
                'course_type' => $c->course->types,
                'course_hours' => 45,
                'teacher_id' => $c->teacher->id,
                'teacher_name' => $c->teacher->name,
            ]),
            'halls' => $halls->map(fn($h) => [
                'hall_name' => $h->hall->name,
                'hall_id' => $h->hall->id,
                'hall_capacity' => $h->hall->capacity,
                'hall_type' => $h->hall->types,
            ]),
            'hall_busy_periods' => $hallBusy->map(fn($s) => [
                'hall_id' => $s->hall->id,
                'hall_name' => $s->hall->name,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'day' => $s->day_of_week,
            ]),
            'break_period' => collect($promptResponse['hard_constraints'])->get('break_period'),
            'operational_period' => collect($promptResponse['hard_constraints'])->get('operational_period'),
            'periods' => collect($promptResponse['hard_constraints'])->get('periods'),
            'soft_constrains' => collect($promptResponse['soft_constraints']),
        ];
    }


    //create timetable draft
    private function createTimetableDraft(array $data, object $currentSchool)
    {
        $semesterId = (string) $data['semester_id'];

        $existingCount = SemesterTimetableDraft::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $semesterId)
            ->count();

        if ($existingCount > 0) {
            throw new AppException(
                "You already have existing timetable draft(s) for this semester. Please select an existing draft to continue editing.",
                409,
                "Existing Drafts Found",
                "Please select an existing draft to continue."
            );
        }

        $timetableDraft = SemesterTimetableDraft::create([
            'name'               => 'Draft 1',
            'school_semester_id' => $semesterId,
            'school_branch_id'   => $currentSchool->id,
            'draft_count'        => 1,
        ]);

        return $timetableDraft;
    }

    private function createTimetableVersion(array $data, object $currentSchool, string $draftId, $schedulerStatus)
    {
        $timetableVersions = SemesterTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->where("draft_id", $draftId)
            ->count();
        $versionNumber = $timetableVersions + 1;
        $timetableVersion = SemesterTimetableVersion::create([
            'name'               => "version {$versionNumber}",
            'parent_version_id' => $data['parent_version_id'] ?? null,
            'version_number'      => $versionNumber,
            'draft_id'           => $draftId,
            'school_branch_id'   => $currentSchool->id,
            'version_count'      => $versionNumber,
            'scheduler_status' => $schedulerStatus
        ]);
        return $timetableVersion;
    }

    private function createTimetableVersionSlots(string $timetableVersionId, object $currentSchool, $schedulerResponse)
    {
        $generatedSlots = $schedulerResponse->timetable;
        foreach ($generatedSlots as $slot) {
            Timetable::create([
                'school_branch_id' => $currentSchool->id,
                'teacher_id' => $slot->teacher_id ?? null,
                'course_id' => $slot->course_id ?? null,
                'hall_id' => $slot->hall_id ?? null,
                'day_of_week' => $slot->day,
                'break' => $slot->break,
                'duration' => $slot->duration,
                'start_time' => Carbon::createFromFormat('H:i', $slot->start_time)->format('H:i:s'),
                'end_time' => Carbon::createFromFormat('H:i', $slot->end_time)->format('H:i:s'),
                'timetable_version_id' => $timetableVersionId,
            ]);
        }
    }

    private function createTimetablePrompts(
        $schedulerResponse,
        array $data,
        string $baseVersionId,
        string $resultVersionId,
        object $currentSchool,
        $schedulerOutput,
        $schedulerInput,
        $aiOutput
    ) {

        SemesterTimetablePrompt::create([
            'school_branch_id' => $currentSchool->id,
            'base_version_id' => $baseVersionId ?? null,
            'result_version_id' => $resultVersionId,
            'prompt' => $data['prompt'],
            'ai_output' => json_encode($aiOutput),
            'scheduler_input' => json_encode($schedulerInput),
            'scheduler_output' => json_encode($schedulerOutput),
        ]);
    }
}
