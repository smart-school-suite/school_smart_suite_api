<?php

namespace App\Http\Controllers\SchoolBranchSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolBranchSetting\UpdateSchoolBranchSettingRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SchoolBranchSetting\SchoolBranchSettingService;
use App\Services\SchoolBranchSetting\ExamSettingService;
use App\Services\SchoolBranchSetting\ElectionSettingService;
use App\Services\SchoolBranchSetting\PromotionSettingService;
use App\Services\SchoolBranchSetting\GradeSettingService;
use App\Services\SchoolBranchSetting\ResitSettingService;
use App\Services\SchoolBranchSetting\TimetableSettingService;

class SchoolBranchSettingController extends Controller
{
    protected SchoolBranchSettingService $schoolBranchSettingService;
    protected ExamSettingService $examSettingService;
    protected ElectionSettingService $electionSettingService;
    protected PromotionSettingService $promotionSettingService;
    protected GradeSettingService $gradeSettingService;
    protected ResitSettingService $resitSettingService;
    protected TimetableSettingService $timetableSettingService;
    public function __construct(
        SchoolBranchSettingService $schoolBranchSettingService,
        ExamSettingService $examSettingService,
        ElectionSettingService $electionSettingService,
        PromotionSettingService $promotionSettingService,
        GradeSettingService $gradeSettingService,
        ResitSettingService $resitSettingService,
        TimetableSettingService $timetableSettingService
    ) {
        $this->schoolBranchSettingService = $schoolBranchSettingService;
        $this->examSettingService = $examSettingService;
        $this->electionSettingService = $electionSettingService;
        $this->promotionSettingService = $promotionSettingService;
        $this->gradeSettingService = $gradeSettingService;
        $this->resitSettingService = $resitSettingService;
        $this->timetableSettingService = $timetableSettingService;
    }
    public function getSchoolBranchSetting(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolBranchSetting = $this->schoolBranchSettingService->getSchoolBranchSetting($currentSchool);
        return ApiResponseService::success("School Branch Setting Fetched Successfully", $schoolBranchSetting, null, 200);
    }
    public function getSchoolBranchSettingDetails(Request $request, $schoolBranchSettingId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolBranchSettingDetails = $this->schoolBranchSettingService->getSchoolBranchSettingDetails($currentSchool, $schoolBranchSettingId);
        return ApiResponseService::success("School Branch Setting Details Fetched Successfully", $schoolBranchSettingDetails, null, 200);
    }
    public function updateExamSetting(UpdateSchoolBranchSettingRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->examSettingService->updateExamSetting($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Exam Setting Updated Successfully", null, null, 200);
    }
    public function updateElectionSetting(UpdateSchoolBranchSettingRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->electionSettingService->updateElectionSetting($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Election Setting Updated Successfully", null, null, 200);
    }
    public function updatePromotionSetting(UpdateSchoolBranchSettingRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->promotionSettingService->updatePromotionSetting($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Student Promotion Setting Updated Successfully", null, null, 200);
    }
    public function updateGradeSetting(UpdateSchoolBranchSettingRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->gradeSettingService->updateGradeSetting($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Grade Setting Updated Successfully", null, null, 200);
    }
    public function updateResitSetting(UpdateSchoolBranchSettingRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->resitSettingService->updateResitSetting($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Resit Setting Updated Successfully", null, null, 200);
    }
    public function updateTimetableSetting(UpdateSchoolBranchSettingRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->resitSettingService->updateResitSetting($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Timetable Setting Updated Successfully", null, null, 200);
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
