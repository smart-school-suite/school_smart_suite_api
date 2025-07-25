<?php

namespace App\Http\Controllers;
use App\Http\Requests\AdditionalFee\AdditionalFeeIdRequest;
use App\Http\Requests\AdditionalFee\AdditionalFeeTransactionIdRequest;
use App\Http\Requests\AdditionalFee\BulkPayAdditionalFeeRequest;
use App\Http\Requests\AdditionalFee\PayAdditionalFeeRequest;
use App\Http\Requests\AdditionalFee\UpdateAdditionalFeeRequest;
use App\Http\Requests\AdditionalFee\CreateAdditionalFeeRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\BulkPayAdditionFeeRequest;
use App\Http\Resources\AdditionalFeeResource;
use App\Http\Resources\AdditionalFeeTransactionResource;
use Illuminate\Support\Facades\Validator;
use App\Services\studentAdditionalFeeService;
use Exception;
use Illuminate\Http\Request;


class StudentAdditionalFeesController extends Controller
{
    //
    protected studentAdditionalFeeService $studentAdditionalFeeService;
    public function __construct(studentAdditionalFeeService $studentAdditionalFeeService)
    {
        $this->studentAdditionalFeeService = $studentAdditionalFeeService;
    }

    public function createStudentAdditionalFees(CreateAdditionalFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createAdditionalFees = $this->studentAdditionalFeeService->createStudentAdditionalFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Additional Fees Created Sucessfully", $createAdditionalFees, null, 201);
    }

    public function getAdditionalFeeDetails(Request $request, $feeId){
        $currenSchool = $request->attributes->get('currentSchool');
        $additionalFeeDetails = $this->studentAdditionalFeeService->getAdditionalFeeDetails($currenSchool, $feeId);
        return ApiResponseService::success("Student Additional Fee Fetched Successfully", $additionalFeeDetails, null, 200);
    }

    public function updateStudentAdditionalFees(UpdateAdditionalFeeRequest $request, string $feeId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateAdditionalFees = $this->studentAdditionalFeeService->updateStudentAdditionalFees($request->validated(), $feeId, $currentSchool);
        return ApiResponseService::success("Student Additional Fees Updated Successfully", $updateAdditionalFees, null, 200);
    }

    public function deleteStudentAdditionalFees(Request $request, string $feeId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteAdditionalFees = $this->studentAdditionalFeeService->deleteStudentAdditionalFees($feeId, $currentSchool);
        return ApiResponseService::success("Student Additional Fee Deleted Sucessfully", $deleteAdditionalFees, null, 200);
    }

    public function getStudentAdditionalFees(Request $request, string $studentId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentAdditionalFees = $this->studentAdditionalFeeService->getStudentAdditionalFees($studentId, $currentSchool);
        return ApiResponseService::success("Student Addtional Fees Fetched Succesfully", $getStudentAdditionalFees, null, 200);
    }

    public function getAdditionalFees(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAdditionalFees = $this->studentAdditionalFeeService->getAdditionalFees($currentSchool);
        return ApiResponseService::success("Student Additional Fees Fetched Successfully", AdditionalFeeResource::collection($getAdditionalFees), null, 200);
    }

    public function payAdditionalFees(PayAdditionalFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payAdditionalFees = $this->studentAdditionalFeeService->payAdditionalFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Additional Fees Paid Successfully", $payAdditionalFees, null, 201);
    }

    public function getAdditionalFeesTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAdditionalFeesTransactions = $this->studentAdditionalFeeService->getAdditionalFeesTransactions($currentSchool);
        return ApiResponseService::success("Student Additional Fees Transactions Fetched Sucessfully", AdditionalFeeTransactionResource::collection($getAdditionalFeesTransactions), null, 200);
    }

    public function reverseAdditionalFeesTransaction(Request $request, string $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $reverseTransaction = $this->studentAdditionalFeeService->reverseTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Successfully", $reverseTransaction, null, 200);
    }

    public function deleteTransaction(Request $request, string $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteTransaction = $this->studentAdditionalFeeService->deleteTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Deleted Successfully", $deleteTransaction, null, 200);
    }

    public function getTransactionDetails(Request $request, string $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $transactionDetails = $this->studentAdditionalFeeService->getTransactionDetail($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Details Fetched Succesfully", $transactionDetails, null, 200);
    }

    public function bulkBillStudents(BulkPayAdditionalFeeRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $bulkBillStudents = $this->studentAdditionalFeeService->bulkBillStudents($request->additional_fee, $currentSchool);
            return ApiResponseService::success("Student Billed Succesfully", $bulkBillStudents, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteStudentAdditionalFees(AdditionalFeeIdRequest $request)
    {
        try {
          $bulkDeleteAdditionalFees = $this->studentAdditionalFeeService->bulkDeleteStudentAdditionalFees($request->feeIds);
          return ApiResponseService::success("Student Additional Fees Deleted Succesfully", $bulkDeleteAdditionalFees, null, 200);
        }
        catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteTransaction(AdditionalFeeTransactionIdRequest $request){
        try{
            $bulkDeleteTransaction = $this->studentAdditionalFeeService->bulkDeleteTransaction($request->transactionIds);
            return ApiResponseService::success("Transaction Deleted Succesfully", $bulkDeleteTransaction, null, 200);
        }
        catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkReverseTransaction(AdditionalFeeTransactionIdRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        try{
            $bulkReverseTransaction = $this->studentAdditionalFeeService->bulkReverseTransaction($request->transactionIds, $currentSchool);
            return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkPayFees(BulkPayAdditionalFeeRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        try{
            $bulkPayFees = $this->studentAdditionalFeeService->bulkPayAdditionalFee($request->additional_fee, $currentSchool);
            return ApiResponseService::success("Additional Fees Paid Succesfully", $bulkPayFees, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
