<?php

namespace App\Http\Controllers\RegistrationFee;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationFee\RegistrationFeeIdRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\RegistrationFee\RegistrationFeeService;

class RegistrationFeeController extends Controller
{
    protected RegistrationFeeService $registrationFeeService;

    public function __construct(RegistrationFeeService $registrationFeeService)
    {
        $this->registrationFeeService = $registrationFeeService;
    }
    public function getRegistrationFees(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $registrationFees = $this->registrationFeeService->getRegistrationFees($currentSchool);
        return ApiResponseService::success("Registration Fees Fetched Successfully", $registrationFees, null, 200);
    }
    public function bulkDeleteRegistrationFee(RegistrationFeeIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $this->registrationFeeService->bulkDeleteRegistrationFee($request->registrationFeeIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Registration Fee Deleted Successfully", null, null, 200);
    }

    public function deleteRegistrationFee(Request $request, $feeId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $this->registrationFeeService->deleteRegistrationFee($feeId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Registration Fee Deleted Successfully", null, null, 200);
    }

    public function registrationFeeDetails(Request $request, $feeId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $registrationFeeDetails = $this->registrationFeeService->getRegistrationFeeDetails($currentSchool, $feeId);
        return ApiResponseService::success("Registration Fee Details Fetched Successfully", $registrationFeeDetails, null, 200);
    }

    public function getStudentRegistrationFee(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authStudent = $this->resolveUser();
        $registrationFees = $this->registrationFeeService->getStudentRegistratonFees($currentSchool, $authStudent);
        return ApiResponseService::success("Student Registration Fees Fetched Successfully", $registrationFees, null, 200);
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
