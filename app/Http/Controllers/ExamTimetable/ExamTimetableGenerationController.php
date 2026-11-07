<?php

namespace App\Http\Controllers\ExamTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamTimetable\GenerateExamTimetableRequest;
use App\Services\ApiResponseService;
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
