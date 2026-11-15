<?php

namespace App\Http\Controllers\ResitTimetable;

use App\Exceptions\ExamTimetable\ExamTimetableActivationConflict;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResitTimetable\GenerateResitTimetableRequest;
use App\Services\ApiResponseService;
use App\Services\ResitTimetable\ActivateResitTimetableService;
use App\Services\ResitTimetable\ResitApplySolutionService;
use App\Services\ResitTimetable\ResitInvigilatorService;
use App\Services\ResitTimetable\ResitTimetableGenerationDispatcher;
use App\Services\ResitTimetable\ResitTimetableInsightService;
use App\Services\ResitTimetable\ResitTimetableResultService;
use App\Services\ResitTimetable\ResitTimetableSetupService;
use App\Services\ResitTimetable\ResitTimetableVersionService;
use Illuminate\Http\Request;

/** Resit timetable generation, versions, solutions, activation and invigilators. */
class ResitTimetableGenerationController extends Controller
{
    public function __construct(protected ResitTimetableResultService $results) {}

    // ── Setup ────────────────────────────────────────────────────────────

    public function getConstraints(ResitTimetableSetupService $setup)
    {
        return ApiResponseService::success('Resit Timetable Constraints Fetched Successfully', $setup->constraints(), null, 200);
    }

    public function getSchedulingData(Request $request, ResitTimetableSetupService $setup, string $resitExamId)
    {
        $data = $setup->schedulingData($this->school($request), $resitExamId);
        return ApiResponseService::success('Resit Scheduling Data Fetched Successfully', $data, null, 200);
    }

    // ── Generation and results ───────────────────────────────────────────

    public function generateTimetable(GenerateResitTimetableRequest $request, ResitTimetableGenerationDispatcher $dispatcher)
    {
        $version = $dispatcher->dispatch($this->school($request), $request->validated(), $this->resolveUser());

        return ApiResponseService::success(
            'Resit timetable generation initiated successfully',
            ['version' => $version->only(['id', 'version_number', 'label', 'scheduler_status', 'resit_exam_id'])],
            null,
            200
        );
    }

    public function getTimetableStatus(Request $request, string $versionId)
    {
        $status = $this->results->getTimetableStatus($versionId, $this->school($request));
        return ApiResponseService::success('Resit Timetable Status Fetched Successfully', ['scheduler_status' => $status], null, 200);
    }

    public function getTimetableSlots(Request $request, string $versionId)
    {
        return ApiResponseService::success('Resit Timetable Slots Fetched Successfully', $this->results->getTimetableSlots($versionId, $this->school($request)), null, 200);
    }

    public function getTimetableRequestPayload(Request $request, string $versionId)
    {
        return ApiResponseService::success('Resit Timetable Request Payload Fetched Successfully', $this->results->getRequestPayload($versionId, $this->school($request)), null, 200);
    }

    public function getRawDiagnostics(Request $request, string $versionId)
    {
        return ApiResponseService::success('Resit Timetable Diagnostics Fetched Successfully', $this->results->getRawDiagnostics($versionId, $this->school($request)), null, 200);
    }

    public function getErrors(Request $request, string $versionId)
    {
        return ApiResponseService::success('Resit Timetable Errors Fetched Successfully', $this->results->getErrors($versionId, $this->school($request)), null, 200);
    }

    // ── Solutions ────────────────────────────────────────────────────────

    public function getTimetableSolutions(Request $request, ResitTimetableInsightService $insights, string $versionId)
    {
        return ApiResponseService::success('Resit Timetable Solutions Fetched Successfully', $insights->solutions($this->school($request), $versionId), null, 200);
    }

    public function applySolutions(Request $request, ResitApplySolutionService $solutions, string $versionId)
    {
        $data = $request->validate([
            'solution_ids' => 'required|array|min:1',
            'solution_ids.*' => 'required|string',
        ]);

        $result = $solutions->apply($this->school($request), $versionId, $data['solution_ids'], $this->resolveUser());
        return ApiResponseService::success('Resit Timetable Solutions Applied Successfully', $result, null, 200);
    }

    // ── Activation ───────────────────────────────────────────────────────

    public function activateTimetableVersion(Request $request, ActivateResitTimetableService $activation, string $versionId)
    {
        try {
            $result = $activation->activate($this->school($request), $versionId);
        } catch (ExamTimetableActivationConflict $conflict) {
            return ApiResponseService::error($conflict->getMessage(), null, 422, [
                'title' => $conflict->getTitle(),
                'description' => $conflict->getDescription(),
                'path' => null,
                'conflicts' => $conflict->conflicts(),
            ]);
        }

        return ApiResponseService::success('Resit Timetable Activated Successfully', $result, null, 200);
    }

    public function checkTimetableActivation(Request $request, ActivateResitTimetableService $activation, string $versionId)
    {
        return ApiResponseService::success('Resit Timetable Activation Checked Successfully', $activation->check($this->school($request), $versionId), null, 200);
    }

    public function getActiveTimetable(Request $request, ActivateResitTimetableService $activation, string $resitExamId)
    {
        return ApiResponseService::success('Active Resit Timetable Fetched Successfully', $activation->activeFor($this->school($request), $resitExamId), null, 200);
    }

    // ── Versions ─────────────────────────────────────────────────────────

    public function getVersions(Request $request, ResitTimetableVersionService $versions, string $resitExamId)
    {
        return ApiResponseService::success('Resit Timetable Versions Fetched Successfully', $versions->versionsFor($this->school($request), $resitExamId), null, 200);
    }

    public function createVersion(Request $request, ResitTimetableVersionService $versions)
    {
        $data = $request->validate(['resit_exam_id' => 'required|string|exists:resit_exams,id']);
        return ApiResponseService::success('Resit Timetable Version Created Successfully', $versions->createVersion($this->school($request), $data), null, 201);
    }

    public function deleteVersion(Request $request, ResitTimetableVersionService $versions, string $versionId)
    {
        return ApiResponseService::success('Resit Timetable Version Deleted Successfully', $versions->deleteVersion($this->school($request), $versionId), null, 200);
    }

    // ── Invigilators ─────────────────────────────────────────────────────

    public function assignInvigilators(Request $request, ResitInvigilatorService $invigilators)
    {
        $data = $request->validate([
            'resit_exam_id' => 'required|string|exists:resit_exams,id',
            'invigilators' => 'required|array|min:1',
            'invigilators.*.actorable_id' => 'required|string',
            'invigilators.*.actorable_type' => 'required|string|max:150',
        ]);
        return ApiResponseService::success('Invigilators Assigned Successfully', $invigilators->assign($this->school($request), $data), null, 201);
    }

    public function removeInvigilators(Request $request, ResitInvigilatorService $invigilators)
    {
        $data = $request->validate([
            'resit_exam_id' => 'required|string|exists:resit_exams,id',
            'invigilator_ids' => 'required|array|min:1',
            'invigilator_ids.*' => 'required|string',
        ]);
        return ApiResponseService::success('Assigned Invigilators Removed Successfully', $invigilators->remove($this->school($request), $data), null, 200);
    }

    public function getInvigilators(Request $request, ResitInvigilatorService $invigilators, string $resitExamId)
    {
        return ApiResponseService::success('Assigned invigilators fetched Successfully', $invigilators->assigned($this->school($request), $resitExamId), null, 200);
    }

    public function getPotentialInvigilators(Request $request, ResitInvigilatorService $invigilators, string $resitExamId)
    {
        return ApiResponseService::success('Potential Invigilators Fetched Successfully', $invigilators->potential($this->school($request), $resitExamId), null, 200);
    }

    private function school(Request $request): object
    {
        return $request->attributes->get('currentSchool');
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
