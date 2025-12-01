<?php

namespace App\Http\Controllers\AdditionalFee;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdditionalFee\AdditionalFeeIdRequest;
use App\Http\Requests\AdditionalFee\BulkBillStudentAdditionalFeeRequest;
use App\Http\Requests\AdditionalFee\BulkUpdateAdditionalFeeRequest;
use App\Http\Requests\AdditionalFee\UpdateAdditionalFeeRequest;
use App\Http\Requests\AdditionalFee\CreateAdditionalFeeRequest;
use App\Services\ApiResponseService;
use App\Http\Resources\AdditionalFeeResource;
use App\Services\AdditionalFee\AdditionalFeeService;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class AdditionalFeeController extends Controller
{
    protected AdditionalFeeService $additionalFeeService;
    public function __construct(
        AdditionalFeeService  $additionalFeeService
    ) {
        $this->additionalFeeService = $additionalFeeService;
    }

    public function createStudentAdditionalFees(CreateAdditionalFeeRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $createAdditionalFees = $this->additionalFeeService->createStudentAdditionalFees($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Additional Fees Created Sucessfully", $createAdditionalFees, null, 201);
    }
    public function getAdditionalFeeDetails(Request $request, $feeId)
    {
        $currenSchool = $request->attributes->get('currentSchool');
        $additionalFeeDetails = $this->additionalFeeService->getAdditionalFeeDetails($currenSchool, $feeId);
        return ApiResponseService::success("Student Additional Fee Fetched Successfully", $additionalFeeDetails, null, 200);
    }
    public function updateStudentAdditionalFees(UpdateAdditionalFeeRequest $request, string $feeId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updateAdditionalFees = $this->additionalFeeService->updateStudentAdditionalFees($request->validated(), $feeId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Additional Fees Updated Successfully", $updateAdditionalFees, null, 200);
    }
    public function deleteStudentAdditionalFees(Request $request, string $feeId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteAdditionalFees = $this->additionalFeeService->deleteStudentAdditionalFees($feeId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Additional Fee Deleted Sucessfully", $deleteAdditionalFees, null, 200);
    }
    public function getStudentAdditionalFeesStudentId(Request $request, string $studentId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentAdditionalFees = $this->additionalFeeService->getStudentAdditionalFeesStudentId($studentId, $currentSchool);
        return ApiResponseService::success("Student Addtional Fees Fetched Succesfully", $getStudentAdditionalFees, null, 200);
    }
    public function getAdditionalFees(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAdditionalFees = $this->additionalFeeService->getAdditionalFees($currentSchool);
        return ApiResponseService::success("Student Additional Fees Fetched Successfully", AdditionalFeeResource::collection($getAdditionalFees), null, 200);
    }
    public function bulkBillStudents(BulkBillStudentAdditionalFeeRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkBillStudents = $this->additionalFeeService->bulkBillStudents($request->fee_details, $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Billed Succesfully", $bulkBillStudents, null, 200);
    }
    public function bulkDeleteStudentAdditionalFees(AdditionalFeeIdRequest $request)
    {
        try {
            $authAdmin = $this->resolveUser();
            $currentSchool = $request->attributes->get('currentSchool');
            $bulkDeleteAdditionalFees = $this->additionalFeeService->bulkDeleteStudentAdditionalFees($request->feeIds, $currentSchool, $authAdmin);
            return ApiResponseService::success("Student Additional Fees Deleted Succesfully", $bulkDeleteAdditionalFees, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkUpdateAdditionalFee(BulkUpdateAdditionalFeeRequest $request)
    {
        try {
            $authAdmin = $this->resolveUser();
            $currentSchool = $request->attributes->get('currentSchool');
            $this->additionalFeeService->bulkUpdateStudentAdditionalFees($request->additional_fee, $currentSchool, $authAdmin);
            return ApiResponseService::success("Additional Fees Updated Successfully", null, null, 200);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function getStudentAdditionalFees(Request $request, $status)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $studentAdditionalFees = $this->additionalFeeService->getStudentAdditionalFees($currentSchool, $authAdmin, $status);
        return ApiResponseService::success("Student Additional Fees Fetched Successfully", $studentAdditionalFees, null, 200);
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
