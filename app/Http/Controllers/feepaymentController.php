<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\FeePaymentService;
use App\Http\Requests\FeePaymentRequest;
use App\Http\Requests\UpdateFeePaymentRequest;
use App\Http\Resources\FeeDebtorResource;
use App\Http\Requests\PayRegistrationFeesRequest;
use App\Http\Requests\BulkPayRegistrationFeeRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PaidFeesResource;
use Exception;
use Illuminate\Http\Request;

class FeePaymentController extends Controller
{
    //
    protected FeePaymentService $feePaymentService;
    public function __construct(FeePaymentService $feePaymentService){
        $this->feePaymentService = $feePaymentService;
    }
    public function payTuitionFees(FeePaymentRequest $request) {
        $currentSchool = $request->attributes->get('currentSchool');
        $payFees = $this->feePaymentService->payStudentFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Fees Paid Sucessfully", $payFees, null, 201);
    }

    public function payRegistrationFees(PayRegistrationFeesRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $payRegistrationFees = $this->feePaymentService->payRegistrationFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Registration Fees Paid Sucessfully", $payRegistrationFees, null, 201);
    }

    public function getRegistrationFees(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $registrationFees = $this->feePaymentService->getRegistrationFees($currentSchool);
        return ApiResponseService::success("Registration Fees Fetched Sucessfully", $registrationFees, null, 200);
    }

    public function getFeesPaid(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $feePaid = $this->feePaymentService->getFeesPaid($currentSchool);
        return ApiResponseService::success('fee payment records fetched successfully', $feePaid, null, 200);
    }

    public function updateFeesPaid(UpdateFeePaymentRequest $request, $fee_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $updateFeesPaid = $this->feePaymentService->updateStudentFeesPayment($request->validated(),$fee_id,$currentSchool);
        return ApiResponseService::success("Fee Payment Record Updated Sucessfully", $updateFeesPaid, null, 200);
    }

    public function deleteFeePaid(Request $request, $fee_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteFeePayment = $this->feePaymentService->deleteFeePayment($fee_id, $currentSchool);
        return ApiResponseService::success('Record Deleted Sucessfully', $deleteFeePayment, null, 200);
    }

    public function getFeeDebtors(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $feeDebtors = $this->feePaymentService->getFeeDebtors($currentSchool);
        return ApiResponseService::success("Fee Debtors Fetched Succefully", FeeDebtorResource::collection($feeDebtors), null, 200);
    }

    public function getTuitionFees(Request $request) {
        $currentSchool = $request->attributes->get('currentSchool');
        $tuitionFees = $this->feePaymentService->getTuitionFees($currentSchool);
        return ApiResponseService::success("Tuition Fees Fetched Successfully", $tuitionFees, null, 200);
    }

    public function getTuitionFeeTransactions(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $transactions = $this->feePaymentService->getTuitionFeeTransactions($currentSchool);
        return ApiResponseService::success("Tuition Fee Transactions fetched Successfully", $transactions, null, 200);
    }

    public function reverseTuitionFeeTransaction(Request $request, $transactionId){
        $currentSchool = $request->attributes->get('currentSchool');
        $reverseTransaction = $this->feePaymentService->reverseFeePaymentTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Successfully", $reverseTransaction, null, 200);
    }

    public function deleteTuitionFeeTransaction(Request $request, $transactionId){
        $currentSchool = $request->attributes->get('currentSchool');
        $this->feePaymentService->deleteTuitionFeeTransaction($transactionId, $currentSchool);
    }

    public function getTuitionTransactionFeeDetails(Request $request, $tranctionId){
        $currentSchool = $request->attributes->get('currentSchool');
        $transactionDetail = $this->feePaymentService->tuitionFeeTransactionDetails($tranctionId, $currentSchool);
        return ApiResponseService::success("Transaction Details Fetched Succesfully", $transactionDetail, null, 200);
    }

    public function getRegistrationFeeTransactions(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $registrationFeeTransactions = $this->feePaymentService->getRegistrationFeeTransactions($currentSchool);
        return ApiResponseService::success("Registration Fee Transactions Fetched Succesfully", $registrationFeeTransactions, null, 200);
    }

    public function reverseRegistrationFeeTransaction(Request $request, $transactionId){
        $currentSchool = $request->attributes->get('currentSchool');
        $reverseTransaction = $this->feePaymentService->reverseRegistrationFeePaymentTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Sucessfully", $reverseTransaction, null, 200);
    }

    public function bulkReverseRegistrationFeeTransaction(Request $request, $transactionIds){
        $idsArray = explode(',', $transactionIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
           return ApiResponseService::error("No IDs provided", null, 422);
       }
       $validator = Validator::make(['ids' => $idsArray], [
           'ids' => 'required|array',
           'ids.*' => 'string|exists:registration_fee_transactions,id',
       ]);
       if ($validator->fails()) {
           return ApiResponseService::error($validator->errors(), null, 422);
       }
        $currentSchool = $request->attributes->get('currentSchool');
        try{
            $bulkReverseTransaction = $this->feePaymentService->bulkReverseRegistrationFeeTransaction($idsArray, $currentSchool);
            return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkReverseTuitionFeeTransaction(Request $request, $transactionIds){
        $idsArray = explode(',', $transactionIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
           return ApiResponseService::error("No IDs provided", null, 422);
       }
       $validator = Validator::make(['ids' => $idsArray], [
           'ids' => 'required|array',
           'ids.*' => 'string|exists:tuition_fee_transactions,id',
       ]);
       if ($validator->fails()) {
           return ApiResponseService::error($validator->errors(), null, 422);
       }
        $currentSchool = $request->attributes->get('currentSchool');
        try{
           $bulkReverseTransaction = $this->feePaymentService->bulkReverseTuitionFeeTransaction($idsArray, $currentSchool);
           return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);

        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkPayRegistrationFee(BulkPayRegistrationFeeRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        try{
            $bulkPayRegistrationFee = $this->feePaymentService->bulkPayRegistrationFee($request->registration_fee, $currentSchool);
            return ApiResponseService::success("Fee Paid Succesfully", $bulkPayRegistrationFee, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteTuitionFeeTransactions($transactionIds){
        $idsArray = explode(',', $transactionIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
           return ApiResponseService::error("No IDs provided", null, 422);
       }
       $validator = Validator::make(['ids' => $idsArray], [
           'ids' => 'required|array',
           'ids.*' => 'string|exists:tuition_fee_transactions,id',
       ]);
       if ($validator->fails()) {
           return ApiResponseService::error($validator->errors(), null, 422);
       }
       try{
          $bulkDelete = $this->feePaymentService->bulkDeleteTuitionFeeTransaction($idsArray);
          return ApiResponseService::success("Tuition Fee Transactions Deleted Successfully", $bulkDelete, null, 200);
       }
       catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
       }
    }

    public function bulkDeleteRegistrationFeeTransactions($transactionIds){
        $idsArray = explode(',', $transactionIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
           return ApiResponseService::error("No IDs provided", null, 422);
       }
       $validator = Validator::make(['ids' => $idsArray], [
           'ids' => 'required|array',
           'ids.*' => 'string|exists:tuition_fee_transactions,id',
       ]);
       if ($validator->fails()) {
           return ApiResponseService::error($validator->errors(), null, 422);
       }
       try{
          $bulkDelete = $this->feePaymentService->bulkDeleteTuitionFeeTransaction($idsArray);
          return ApiResponseService::success("Transactions Deleted Succesfully", $bulkDelete, null, 200);
       }
       catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
       }
    }
}
