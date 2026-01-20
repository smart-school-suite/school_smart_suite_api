<?php

namespace App\Http\Controllers\ActivationCode;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivationCode\ActivateStudentAccountRequest;
use App\Http\Requests\ActivationCode\ActivateTeacherAccountRequest;
use App\Http\Requests\ActivationCode\PurchaseActivationCodeRequest;
use Illuminate\Http\Request;
use App\Services\ActivationCode\ActivationCodeService;
use App\Services\ApiResponseService;

class ActivationCodeController extends Controller
{
    protected ActivationCodeService $activationCodeService;
    public function __construct(ActivationCodeService $activationCodeService)
    {
        $this->activationCodeService = $activationCodeService;
    }
    public function purchaseActivationCode(PurchaseActivationCodeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $purchaseCodes = $this->activationCodeService->purchaseActivationCode($request->validated(), $currentSchool, $this->resolveUser());
        return ApiResponseService::success("Activation Code Purchased Successfully", $purchaseCodes, null, 200);
    }
    public function getSchoolBranchActivationCodes(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $activationCodes = $this->activationCodeService->getSchoolBranchActivationCodes($currentSchool);
        return ApiResponseService::success("Activation Code Fetched Successfully", $activationCodes, null, 200);
    }
    public function activateStudentAccount(ActivateStudentAccountRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $activateStudent = $this->activationCodeService->activateStudentAccount($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Account Activated Successfully", $activateStudent, null, 200);
    }
    public function activateTeacherAccount(ActivateTeacherAccountRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $activateTeacher = $this->activationCodeService->activateTeacherAccount($request->validated(), $currentSchool);
        return ApiResponseService::success("Teacher Account Activated Successfully", $activateTeacher, null, 200);
    }

    public function getActivationCodeUsage(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $activationCodeUsage = $this->activationCodeService->getActivationCodeUsage($currentSchool);
        return ApiResponseService::success("Activation Code Usages Fetched Successfully", $activationCodeUsage, null, 200);
    }

    public function getStudentActivationCodeStatus(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $status = $this->activationCodeService->getStudentActivationStatuses($currentSchool);
        return ApiResponseService::success("Student Activation Code Status Fetched Successfully", $status, null, 200);
    }

    public function getTeacherActivationCodeStatus(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $status = $this->activationCodeService->getTeacherActivationStatuses($currentSchool);
        return ApiResponseService::success("Teacher Activation Code Status Fetched Successfully", $status, null, 200);
    }

    public function getStudentSubscriptionDetail(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $subscriptionDetail = $this->activationCodeService->getStudentSubscriptionDetail($currentSchool, $studentId);
        return ApiResponseService::success("Student Subscription Detail Fetched Successfully", $subscriptionDetail, null, 200);
    }

    public function getTeacherSubscriptionDetail(Request $request, $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $subscriptionDetail = $this->activationCodeService->getTeacherSubscriptionDetail($currentSchool, $teacherId);
        return ApiResponseService::success("Teacher Subscription Detail Fetched Successfully", $subscriptionDetail, null, 200);
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
