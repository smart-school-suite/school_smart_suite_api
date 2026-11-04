<?php

namespace App\Http\Controllers\ExamTimetable;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Http\Requests\ExamTimetable\CreateExamTimetableRequest;
use App\Http\Requests\ExamTimetable\UpdateExamTimetableRequest;
use App\Exceptions\ExamTimetable\ExamTimetableActivationConflict;
use App\Http\Requests\ExamTimetable\GenerateExamTimetableRequest;
use App\Services\ExamTimetable\ActivateExamTimetableService;
use App\Services\ExamTimetable\ApplyExamSolutionService;
use App\Services\ExamTimetable\ExamTimetableGenerationDispatcher;
use App\Services\ExamTimetable\ExamTimetableInsightService;
use App\Services\ExamTimetable\ExamTimetableResultService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ExamTimetable\ExamTimetableService;

class ExamTimetableController extends Controller
{
    protected ExamTimetableService $examTimeTableService;
    protected ExamTimetableResultService $resultService;

    public function __construct(
        ExamTimetableService $examTimeTableService,
        ExamTimetableResultService $resultService
    ) {
        $this->examTimeTableService = $examTimeTableService;
        $this->resultService = $resultService;
    }

    // ---------------------------------------------------------------------
    // Scheduler: generate, inspect, solve and activate (same flow as the semester timetable)
    // ---------------------------------------------------------------------

    public function generate(GenerateExamTimetableRequest $request, ExamTimetableGenerationDispatcher $dispatcher): JsonResponse
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $version = $dispatcher->dispatch($currentSchool, $request->validated(), $this->resolveUser());

        return ApiResponseService::success(
            "Exam Timetable Generation Initiated Successfully",
            ['version' => $version->only(['id', 'version_number', 'label', 'scheduler_status', 'exam_id'])],
            null,
            Response::HTTP_OK
        );
    }

    public function getTimetableStatus(Request $request, string $versionId): JsonResponse
    {
        $status = $this->resultService->getTimetableStatus($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Status Fetched Successfully", $status, null, Response::HTTP_OK);
    }

    public function getErrors(Request $request, string $versionId): JsonResponse
    {
        $errors = $this->resultService->getErrors($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Errors Fetched Successfully", $errors, null, Response::HTTP_OK);
    }

    public function getTimetableSlots(Request $request, string $versionId): JsonResponse
    {
        $slots = $this->resultService->getTimetableSlots($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Slots Fetched Successfully", $slots, null, Response::HTTP_OK);
    }

    public function getTimetableRequestPayload(Request $request, string $versionId): JsonResponse
    {
        $payload = $this->resultService->getRequestPayload($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Exam Timetable Request Payload Fetched Successfully", $payload, null, Response::HTTP_OK);
    }

    public function getParsedDiagnostics(Request $request, string $versionId): JsonResponse
    {
        $diagnostics = $this->resultService->getParsedDiagnostics($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Parsed Exam Timetable Diagnostics Fetched Successfully", $diagnostics, null, Response::HTTP_OK);
    }

    public function getRawDiagnostics(Request $request, string $versionId): JsonResponse
    {
        $diagnostics = $this->resultService->getRawDiagnostics($versionId, $request->attributes->get('currentSchool'));
        return ApiResponseService::success("Raw Exam Timetable Diagnostics Fetched Successfully", $diagnostics, null, Response::HTTP_OK);
    }

    public function getTimetableSolutions(Request $request, ExamTimetableInsightService $insights, string $versionId): JsonResponse
    {
        $solutions = $insights->solutions($request->attributes->get('currentSchool'), $versionId);
        return ApiResponseService::success("Exam Timetable Solutions Fetched Successfully", $solutions, null, Response::HTTP_OK);
    }

    public function applySolutions(Request $request, ApplyExamSolutionService $solutions, string $versionId): JsonResponse
    {
        $data = $request->validate(['solution_ids' => 'required|array|min:1', 'solution_ids.*' => 'required|string']);
        $result = $solutions->apply($request->attributes->get('currentSchool'), $versionId, $data['solution_ids'], $this->resolveUser());
        return ApiResponseService::success("Solutions Applied Successfully", $result, null, Response::HTTP_OK);
    }

    public function createActiveExamTimetable(Request $request, ActivateExamTimetableService $activation): JsonResponse
    {
        $data = $request->validate(['timetable_version_id' => 'required|string']);

        return $this->activateTimetableVersion($request, $activation, $data['timetable_version_id']);
    }

    public function activateTimetableVersion(Request $request, ActivateExamTimetableService $activation, string $versionId): JsonResponse
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

        return ApiResponseService::success("Exam Timetable Activated Successfully", $result, null, Response::HTTP_OK);
    }

    public function checkTimetableActivation(Request $request, ActivateExamTimetableService $activation, string $versionId): JsonResponse
    {
        $check = $activation->check($request->attributes->get('currentSchool'), $versionId);
        return ApiResponseService::success("Exam Timetable Activation Checked Successfully", $check, null, Response::HTTP_OK);
    }

    public function getActiveTimetable(Request $request, ActivateExamTimetableService $activation, string $examId): JsonResponse
    {
        $active = $activation->activeFor($request->attributes->get('currentSchool'), $examId);
        return ApiResponseService::success("Active Exam Timetable Fetched Successfully", $active, null, Response::HTTP_OK);
    }

    // ---------------------------------------------------------------------
    // Manual exam timetable
    // ---------------------------------------------------------------------
    public function createTimetable(CreateExamTimetableRequest $request, string $examId): JsonResponse
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $createdExamTimeTable = $this->examTimeTableService->createExamTimeTable($request->entries, $currentSchool, $examId, $authAdmin);
        return ApiResponseService::success("Timetable Created Successfully", $createdExamTimeTable, null, Response::HTTP_CREATED);
    }

    public function deleteTimetableEntry(Request $request, string $entryId): JsonResponse
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $currentSchool = $request->attributes->get('currentSchool');
        $deletedExamTimeTableEntry = $this->examTimeTableService->deleteTimetableEntry($entryId, $currentSchool, $authAdmin);

        return ApiResponseService::success("Exam Timetable Entry Deleted Successfully", $deletedExamTimeTableEntry, null, Response::HTTP_OK);
    }
    public function deleteTimetable(Request $request, string $examId): JsonResponse
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $deletedTimetable = $this->examTimeTableService->deleteExamTimetable($examId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Exam Timetable Deleted Successfully", $deletedTimetable, null, Response::HTTP_OK);
    }

    public function updateTimetable(UpdateExamTimetableRequest $request): JsonResponse
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $updatedExamTimetable = $this->examTimeTableService->updateExamTimetable($request->entries, $currentSchool, $authAdmin);
        return ApiResponseService::success("Exam Timetable Updated Successfully", $updatedExamTimetable, null, Response::HTTP_OK);
    }

    public function generateExamTimetable(Request $request): JsonResponse
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route('examId');
        $generatedExamTimeTable = $this->examTimeTableService->generateExamTimeTable($examId, $currentSchool);
        return ApiResponseService::success("Exam Timetable Generated Successfully", $generatedExamTimeTable, null, Response::HTTP_OK);
    }
    public function prepareExamTimeTableData(Request $request, string $examId): JsonResponse
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $preparedExamTimeTableData = $this->examTimeTableService->prepareExamTimeTableData($examId, $currentSchool);
        return ApiResponseService::success("Exam Timetable Data Fetched Successfully", $preparedExamTimeTableData, null, Response::HTTP_OK);
    }

    public function getExamTimetableStudentIdExamId(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $studentId = $request->route('studentId');
        $examId = $request->route('examId');
        $examTimetable = $this->examTimeTableService->getExamTimetableStudentIdExamId($currentSchool, $studentId, $examId);
        return ApiResponseService::success("Exam Timetable Fetched Successfully", $examTimetable, null, 200);
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
