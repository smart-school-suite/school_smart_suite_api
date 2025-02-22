<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddtionalFeesRequest;
use App\Http\Requests\UpdateAdditionalFees;
use App\Services\ApiResponseService;
use App\Http\Requests\PayAdditionalFeesRequest;
use App\Services\studentAdditionalFeeService;
use Illuminate\Http\Request;


class StudentAdditionalFeesController extends Controller
{
    //
    protected studentAdditionalFeeService $studentAdditionalFeeService;
    public function __construct(studentAdditionalFeeService $studentAdditionalFeeService){
        $this->studentAdditionalFeeService = $studentAdditionalFeeService;
    }

    public function createStudentAdditionalFees(AddtionalFeesRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $createAdditionalFees = $this->studentAdditionalFeeService->createStudentAdditionalFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Additional Fees Created Sucessfully", $createAdditionalFees, null, 201);
    }

    public function updateStudentAdditionalFees(UpdateAdditionalFees $request, string $feeId){
        $currentSchool = $request->attributes->get('currentSchool');
        $updateAdditionalFees = $this->studentAdditionalFeeService->updateStudentAdditionalFees($request->validated(), $feeId, $currentSchool);
        return ApiResponseService::success("Student Additional Fees Updated Successfully", $updateAdditionalFees, null, 200);
    }

    public function deleteStudentAdditionalFees(Request $request, string $feeId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteAdditionalFees = $this->studentAdditionalFeeService->deleteStudentAdditionalFees($currentSchool, $feeId);
        return ApiResponseService::success("Student Additional Fee Deleted Sucessfully", $deleteAdditionalFees, null, 200);
    }

    public function getStudentAdditionalFees(Request $request, string $studentId){
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentAdditionalFees = $this->studentAdditionalFeeService->getStudentAdditionalFees($studentId, $currentSchool);
        return ApiResponseService::success("Student Addtional Fees Fetched Succesfully", $getStudentAdditionalFees, null, 200);
    }

    public function getAdditionalFees(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $getAdditionalFees = $this->studentAdditionalFeeService->getAdditionalFees($currentSchool);
        return ApiResponseService::success("Student Additional Fees Fetched Successfully", $getAdditionalFees, null, 200);
    }

    public function payAdditionalFees(PayAdditionalFeesRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $payAdditionalFees = $this->studentAdditionalFeeService->payAdditionalFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Additional Fees Paid Successfully", $payAdditionalFees, null, 201);
    }

    public function getAdditionalFeesTransactions(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $getAdditionalFeesTransactions = $this->studentAdditionalFeeService->getAdditionalFeesTransactions($currentSchool);
        return ApiResponseService::success("Student Additional Fees Transactions Fetched Sucessfully", $getAdditionalFeesTransactions, null, 200);
    }

}
