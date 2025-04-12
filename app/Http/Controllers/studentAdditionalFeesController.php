<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddtionalFeesRequest;
use App\Http\Requests\UpdateAdditionalFees;
use App\Services\ApiResponseService;
use App\Http\Requests\BulkBillStudentRequest;
use App\Http\Requests\PayAdditionalFeesRequest;
use App\Http\Requests\BulkPayAdditionFeeRequest;
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

    public function createStudentAdditionalFees(AddtionalFeesRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createAdditionalFees = $this->studentAdditionalFeeService->createStudentAdditionalFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Additional Fees Created Sucessfully", $createAdditionalFees, null, 201);
    }

    public function updateStudentAdditionalFees(UpdateAdditionalFees $request, string $feeId)
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
        return ApiResponseService::success("Student Additional Fees Fetched Successfully", $getAdditionalFees, null, 200);
    }

    public function payAdditionalFees(PayAdditionalFeesRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payAdditionalFees = $this->studentAdditionalFeeService->payAdditionalFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Additional Fees Paid Successfully", $payAdditionalFees, null, 201);
    }

    public function getAdditionalFeesTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAdditionalFeesTransactions = $this->studentAdditionalFeeService->getAdditionalFeesTransactions($currentSchool);
        return ApiResponseService::success("Student Additional Fees Transactions Fetched Sucessfully", $getAdditionalFeesTransactions, null, 200);
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

    public function bulkBillStudents(BulkBillStudentRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $bulkBillStudents = $this->studentAdditionalFeeService->bulkBillStudents($request->additional_fee, $currentSchool);
            return ApiResponseService::success("Student Billed Succesfully", $bulkBillStudents, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteStudentAdditionalFees($additionalFeeIds)
    {
        $idsArray = explode(',', $additionalFeeIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:additional_fees,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try {
          $bulkDeleteAdditionalFees = $this->studentAdditionalFeeService->bulkDeleteStudentAdditionalFees($idsArray);
          return ApiResponseService::success("Student Additional Fees Deleted Succesfully", $bulkDeleteAdditionalFees, null, 200);
        }
        catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteTransaction($transactionIds){
        $idsArray = explode(',', $transactionIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:additional_fee_transactions,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
            $bulkDeleteTransaction = $this->studentAdditionalFeeService->bulkDeleteTransaction($idsArray);
            return ApiResponseService::success("Transaction Deleted Succesfully", $bulkDeleteTransaction, null, 200);
        }
        catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkReverseTransaction(Request $request, $transactionIds){
        $currentSchool = $request->attributes->get('currentSchool');
        $idsArray = explode(',', $transactionIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:additional_fee_transactions,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
            $bulkReverseTransaction = $this->studentAdditionalFeeService->bulkReverseTransaction($idsArray, $currentSchool);
            return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkPayFees(BulkPayAdditionFeeRequest $request){
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
