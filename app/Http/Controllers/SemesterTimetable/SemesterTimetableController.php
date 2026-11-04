<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterTimetable\GenerateSemesterTimetableRequest;
use App\Services\ApiResponseService;
use App\Exceptions\SemesterTimetable\TimetableActivationConflict;
use App\Services\SemesterTimetable\ActivateSemesterTimetableService;
use App\Services\SemesterTimetable\ApplySolutionService;
use App\Services\SemesterTimetable\GeneratePreferenceSemesterTimetableService;
use App\Services\SemesterTimetable\SemesterTimetableService;
use App\Services\SemesterTimetable\TimetableGenerationDispatcher;
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

    public function generateTimetable(GenerateSemesterTimetableRequest $request, TimetableGenerationDispatcher $dispatcher)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $version = $dispatcher->dispatch($currentSchool, $request->validated(), $this->resolveUser());

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

    public function createActiveSemesterTimetable(Request $request, ActivateSemesterTimetableService $activation)
    {
        $data = $request->validate([
            'timetable_version_id' => 'required|string',
        ]);

        return $this->activateTimetableVersion($request, $activation, $data['timetable_version_id']);
    }

    public function activateTimetableVersion(Request $request, ActivateSemesterTimetableService $activation, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');

        try {
            $result = $activation->activate($currentSchool, $versionId);
        } catch (TimetableActivationConflict $conflict) {
            return ApiResponseService::error($conflict->getMessage(), null, 422, [
                'title' => $conflict->getTitle(),
                'description' => $conflict->getDescription(),
                'path' => null,
                'conflicts' => $conflict->conflicts(),
            ]);
        }

        return ApiResponseService::success("Timetable Activated Successfully", $result, null, 200);
    }

    public function checkTimetableActivation(Request $request, ActivateSemesterTimetableService $activation, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $check = $activation->check($currentSchool, $versionId);
        return ApiResponseService::success("Timetable Activation Checked Successfully", $check, null, 200);
    }

    public function getActiveTimetable(Request $request, ActivateSemesterTimetableService $activation, string $schoolSemesterId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $active = $activation->activeFor($currentSchool, $schoolSemesterId);
        return ApiResponseService::success("Active Timetable Fetched Successfully", $active, null, 200);
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

    public function getTimetableSolutions(Request $request, TimetableInsightService $insights, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $solutions = $insights->solutions($currentSchool, $versionId);
        return ApiResponseService::success("Timetable Solutions Fetched Successfully", $solutions, null, 200);
    }

    public function applySolutions(Request $request, ApplySolutionService $solutions, string $versionId)
    {
        $data = $request->validate(['solution_ids' => 'required|array|min:1', 'solution_ids.*' => 'required|string']);
        $currentSchool = $request->attributes->get('currentSchool');
        $result = $solutions->apply($currentSchool, $versionId, $data['solution_ids'], $this->resolveUser());
        return ApiResponseService::success("Solutions Applied Successfully", $result, null, 200);
    }
}
