<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterTimetable\GenerateSemesterTimetableRequest;
use App\Jobs\SemesterTimetable\GenerateFixedSemesterTimetable;
use App\Jobs\SemesterTimetable\GeneratePreferenceSemesterTimetable;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Models\Job\SystemJob;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolSemester;
use App\Services\ApiResponseService;
use App\Services\SemesterTimetable\CreateActiveSemesterTimetableService;
use App\Services\SemesterTimetable\GeneratePreferenceSemesterTimetableService;
use App\Services\SemesterTimetable\SemesterTimetableService;
use App\Services\SemesterTimetable\TimetableInsightService;
use Illuminate\Http\Request;

class SemesterTimetableController extends Controller
{
    protected GeneratePreferenceSemesterTimetableService $preferenceTimetableService;
    protected SemesterTimetableService $semesterTimetableService;

    public function __construct(
        GeneratePreferenceSemesterTimetableService $preferenceTimetableService,
        SemesterTimetableService $semesterTimetableService
    ) {
        $this->preferenceTimetableService = $preferenceTimetableService;
        $this->semesterTimetableService = $semesterTimetableService;
    }

    public function generateTimetable(GenerateSemesterTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $timetableSettings = $this->getTimetableSetting($currentSchool);
        if (!empty($timetableSettings['timetable.ignore_teacher_preference'] ?? null)) {
            return $this->generateFixedTimetable($currentSchool, $request);
        } else {
            return $this->generatePreferenceTimetable($currentSchool, $request);
        }
    }

    protected function generatePreferenceTimetable(object $currentSchool, object $request)
    {
        return $this->dispatchGeneration(GeneratePreferenceSemesterTimetable::class, $currentSchool, $request);
    }
    protected function generateFixedTimetable(object $currentSchool, object $request)
    {
        return $this->dispatchGeneration(GenerateFixedSemesterTimetable::class, $currentSchool, $request);
    }

    private function dispatchGeneration(string $jobClass, object $currentSchool, object $request)
    {
        $payload = $request->validated();

        $version = !empty($payload['version_id'])
            ? SemesterTimetableVersion::where('school_branch_id', $currentSchool->id)->find($payload['version_id'])
            : null;
        $version ??= SemesterTimetableVersion::createNext($currentSchool->id, $payload['school_semester_id']);
        $version->update(['scheduler_status' => SemesterTimetableVersion::STATUS_GENERATING]);
        $payload['version_id'] = $version->id;

        $systemJob = SystemJob::create([
            'type' => "Semester Timetable Generation",
            'context_type' => SchoolSemester::class,
            'stage' => "Processing",
            'context_id' => $payload['school_semester_id'],
            'initiated_by_id' => $this->resolveUser()->id,
            'initiated_by_type' => $this->resolveUser()::class,
            'queue' => "database",
            'started_at' => now(),
            'payload' => $payload,
        ]);

        $jobClass::dispatch($currentSchool, $payload, $systemJob->id);

        return ApiResponseService::success(
            "Timetable generation initiated successfully",
            ['version' => $version->only(['id', 'version_number', 'label', 'scheduler_status'])],
            null,
            200
        );
    }

    public function generateTimetableWithPreference(GenerateSemesterTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $engineService = app(GeneratePreferenceSemesterTimetableService::class);
        $response = $engineService->generateTimetable($request->validated(), $currentSchool);
        return ApiResponseService::success("Timetable generated successfully", $response, null, 200);
    }

    public function createActiveSemesterTimetable(
        Request $request,
        CreateActiveSemesterTimetableService $createActiveSemesterTimetable
    ) {
        $currentSchool = $request->attributes->get('currentSchool');
        $data = $request->validate([
            'school_semester_id' => 'required|string',
        ]);
        $activeTimetable = $createActiveSemesterTimetable->createActiveSemesterTimetable($currentSchool, $data);
        return ApiResponseService::success("Active Semester Timetable Created Successfully", $activeTimetable, null, 201);
    }

    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }

    private function getTimetableSetting(object $currentSchool)
    {
        $settings = SchoolBranchSetting::where("school_branch_id", $currentSchool->id)
            ->with(['settingDefination'])
            ->whereHas('settingDefination', function ($query) {
                $query->whereIn('key', ['timetable.ignore_teacher_preference', 'timetable.respect_teacher_preference']);
            })
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->settingDefination->key => $setting->value];
            });
        return $settings;
    }
    public function getTimetableStatus(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $status = $this->semesterTimetableService->getTimetableStatus($versionId, $currentSchool);
        return ApiResponseService::success("Timetable Status Fetched Successfully", $status, null, 200);
    }
    public function getTimetableSlots(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $slots = $this->semesterTimetableService->getTimetableSlots($versionId, $currentSchool);
        return ApiResponseService::success("Timetable Slots Fetched Successfully", $slots, null, 200);
    }
    public function getTimetableRequestPayload(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payload = $this->semesterTimetableService->getRequestPayload($versionId, $currentSchool);
        return ApiResponseService::success("Timetable Request Payload Fetched Successfully", $payload, null, 200);
    }

    public function getParsedDiagnostics(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $parsedDiagnostics = $this->semesterTimetableService->getParsedDiagnostics($versionId, $currentSchool);
        return ApiResponseService::success("Parsed Timetable Diagnostics Fetched Successfully", $parsedDiagnostics, null, 200);
    }

    public function getRawDiagnostics(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $rawDiagnostics = $this->semesterTimetableService->getRawDiagnostics($versionId, $currentSchool);
        return ApiResponseService::success("Raw Timetable Diagnostics Fetched Successfully", $rawDiagnostics, null, 200);
    }

    public function getErrors(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $errors = $this->semesterTimetableService->getErrors($versionId, $currentSchool);
        return ApiResponseService::success("Timetable Errors Fetched Successfully", $errors, null, 200);
    }

    public function getTimetableIssues(Request $request, TimetableInsightService $insights, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $issues = $insights->issues($currentSchool, $versionId);
        return ApiResponseService::success("Timetable Issues Fetched Successfully", $issues, null, 200);
    }

    public function getTimetableSolutions(Request $request, TimetableInsightService $insights, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $solutions = $insights->solutions($currentSchool, $versionId);
        return ApiResponseService::success("Timetable Solutions Fetched Successfully", $solutions, null, 200);
    }

    public function getIssueSolutions(Request $request, TimetableInsightService $insights, string $versionId, string $issueId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $solutions = $insights->solutions($currentSchool, $versionId, $issueId);
        return ApiResponseService::success("Issue Solutions Fetched Successfully", $solutions, null, 200);
    }
}
