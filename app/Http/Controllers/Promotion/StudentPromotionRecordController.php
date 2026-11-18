<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promotion\BulkPromotionDecisionRequest;
use App\Http\Requests\Promotion\PromoteStudentRequest;
use App\Http\Resources\Promotion\PromotionRecordResource;
use App\Services\ApiResponseService;
use App\Services\Promotion\StudentPromotionRecordService;
use Illuminate\Http\Request;

class StudentPromotionRecordController extends Controller
{
    protected StudentPromotionRecordService $studentPromotionRecordService;
    public function __construct(StudentPromotionRecordService $studentPromotionRecordService)
    {
        $this->studentPromotionRecordService = $studentPromotionRecordService;
    }
    public function getStudentPromotionRecords(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $promotionRecords = $this->studentPromotionRecordService->getStudentPromotionRecords($currentSchool);
        return ApiResponseService::success("Promotion Records Fetched Successfully", PromotionRecordResource::collection($promotionRecords), null, 200);
    }
    public function getStudentPromotionDetails(Request $request, string $recordId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $promotionRecord = $this->studentPromotionRecordService->getStudentPromotionRecordDetail($currentSchool, $recordId);
        return ApiResponseService::success("Promotion Record Details Fetched Successfully", $promotionRecord, null, 200);
    }
    public function getNextPromotionSpecialty(Request $request, string $specialtyId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $nextSpecialty = $this->studentPromotionRecordService->getNextPromotionSpecialty($currentSchool, $specialtyId);
        return ApiResponseService::success("Next Level Specialty Fetched Successfully", $nextSpecialty, null, 200);
    }
    public function getStudentPromotionLog(Request $request, string $recordId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $promotionLog = $this->studentPromotionRecordService->getStudentPromotionLog($currentSchool, $recordId);
        return ApiResponseService::success("Student Promotion Log Fetched Successfully", $promotionLog, null, 200);
    }
    public function repeatStudent(PromoteStudentRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authUser = $this->resolveUser();
        $repeatStudent = $this->studentPromotionRecordService->repeatStudent($currentSchool, $request->validated(), $authUser);
        return ApiResponseService::success("Student Repeated Successfully", $repeatStudent, null, 200);
    }
    public function promoteStudent(PromoteStudentRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authUser = $this->resolveUser();
        $promoteStudent = $this->studentPromotionRecordService->promoteStudent($currentSchool, $request->validated(), $authUser);
        return ApiResponseService::success("Student Promoted Successfully", $promoteStudent, null, 200);
    }
    public function bulkPromoteStudents(BulkPromotionDecisionRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $result = $this->studentPromotionRecordService->bulkPromoteStudents($currentSchool, $request->validated(), $this->resolveUser());
        return ApiResponseService::success($this->summary($result, 'promoted'), $result, null, 200);
    }
    public function bulkRepeatStudents(BulkPromotionDecisionRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $result = $this->studentPromotionRecordService->bulkRepeatStudents($currentSchool, $request->validated(), $this->resolveUser());
        return ApiResponseService::success($this->summary($result, 'set to repeat'), $result, null, 200);
    }
    private function summary(array $result, string $verb): string
    {
        $done = count($result['done']);
        $skipped = count($result['skipped']);
        return "{$done} student" . ($done === 1 ? '' : 's') . " {$verb}" . ($skipped ? ", {$skipped} skipped" : '') . '.';
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
