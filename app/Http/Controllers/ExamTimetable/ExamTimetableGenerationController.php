<?php

namespace App\Http\Controllers\ExamTimetable;

use App\Http\Controllers\Controller;
use App\Exceptions\ExamTimetable\ExamTimetableActivationConflict;
use App\Http\Requests\ExamTimetable\GenerateExamTimetableRequest;
use App\Services\ApiResponseService;
use App\Services\ExamTimetable\ActivateExamTimetableService;
use App\Services\ExamTimetable\ExamApplySolutionService;
use App\Services\ExamTimetable\ExamTimetableInsightService;
use App\Services\ExamTimetable\ExamTimetableSetupService;
use App\Services\ExamTimetable\ExamTimetableGenerationDispatcher;
use App\Services\ExamTimetable\ExamTimetableResultService;
use Illuminate\Http\Request;

class ExamTimetableGenerationController extends Controller
{
    public function __construct(protected ExamTimetableResultService $results) {}

    public function generateTimetable(GenerateExamTimetableRequest $request, ExamTimetableGenerationDispatcher $dispatcher)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $version = $dispatcher->dispatch($currentSchool, $request->validated(), $this->resolveUser());

        return ApiResponseService::success(
            "Exam timetable generation initiated successfully",
            ['version' => $version->only(['id', 'version_number', 'label', 'scheduler_status', 'exam_id'])],
            null,
            200
        );
    }

    public function getTimetableStatus(Request $request, string $versionId)
    {
        $status = $this->results->getTimetableStatus($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Status Fetched Successfully", ['scheduler_status' => $status], null, 200);
    }

    public function getTimetableSlots(Request $request, string $versionId)
    {
        $slots = $this->results->getTimetableSlots($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Slots Fetched Successfully", $slots, null, 200);
    }

    public function getTimetableRequestPayload(Request $request, string $versionId)
    {
        $payload = $this->results->getRequestPayload($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Request Payload Fetched Successfully", $payload, null, 200);
    }

    public function getRawDiagnostics(Request $request, string $versionId)
    {
        $diagnostics = $this->results->getRawDiagnostics($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Diagnostics Fetched Successfully", $diagnostics, null, 200);
    }

    public function getErrors(Request $request, string $versionId)
    {
        $errors = $this->results->getErrors($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Errors Fetched Successfully", $errors, null, 200);
    }

    public function getConstraints(ExamTimetableSetupService $setup)
    {
        return ApiResponseService::success("Exam Timetable Constraints Fetched Successfully", $setup->constraints(), null, 200);
    }

    public function getSchedulingData(Request $request, ExamTimetableSetupService $setup, string $examId)
    {
        $data = $setup->schedulingData($request->attributes->get('currentSchool'), $examId);
        return ApiResponseService::success("Exam Scheduling Data Fetched Successfully", $data, null, 200);
    }

    public function getTimetableSolutions(Request $request, ExamTimetableInsightService $insights, string $versionId)
    {
        $solutions = $insights->solutions($request->attributes->get('currentSchool'), $versionId);
        return ApiResponseService::success("Exam Timetable Solutions Fetched Successfully", $solutions, null, 200);
    }

    public function applySolutions(Request $request, ExamApplySolutionService $solutions, string $versionId)
    {
        $data = $request->validate([
            'solution_ids' => 'required|array|min:1',
            'solution_ids.*' => 'required|string',
        ]);

        $result = $solutions->apply($request->attributes->get('currentSchool'), $versionId, $data['solution_ids'], $this->resolveUser());
        return ApiResponseService::success("Exam Timetable Solutions Applied Successfully", $result, null, 200);
    }

    public function activateTimetableVersion(Request $request, ActivateExamTimetableService $activation, string $versionId)
    {
        try {
            $result = $activation->activate($request->attributes->get('currentSchool'), $versionId);
        } catch (ExamTimetableActivationConflict $conflict) {
            return ApiResponseService::error($conflict->getMessage(), null, 422, [
                'title' => $conflict->getTitle(),
                'description' => $conflict->getDescription(),
                'path' => null,
                'conflicts' => $conflict->conflicts(),
            ]);
        }

        return ApiResponseService::success("Exam Timetable Activated Successfully", $result, null, 200);
    }

    public function checkTimetableActivation(Request $request, ActivateExamTimetableService $activation, string $versionId)
    {
        $check = $activation->check($request->attributes->get('currentSchool'), $versionId);
        return ApiResponseService::success("Exam Timetable Activation Checked Successfully", $check, null, 200);
    }

    public function getActiveTimetable(Request $request, ActivateExamTimetableService $activation, string $examId)
    {
        $active = $activation->activeFor($request->attributes->get('currentSchool'), $examId);
        return ApiResponseService::success("Active Exam Timetable Fetched Successfully", $active, null, 200);
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
}
