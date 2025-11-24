<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationFee\RegistrationFeeIdRequest;
use App\Services\ApiResponseService;
use App\Services\FeePaymentService;
use App\Http\Resources\FeeDebtorResource;
use App\Http\Requests\RegistrationFee\BulkPayRegistrationFeeRequest;
use App\Http\Requests\RegistrationFee\PayRegistrationFeeRequest;
use App\Http\Requests\RegistrationFee\RegistrationFeeTransactionIdRequest;
use App\Http\Requests\TuitionFee\BulkPayTuitionFeeRequest;
use App\Http\Requests\TuitionFee\PayTuitionFeeRequest;
use App\Http\Requests\TuitionFee\TuitionFeeTransactionIdRequest;
use App\Http\Requests\TuitionFee\UpdateTuitionFeePaymentRequest;
use App\Http\Resources\RegistrationFeeResource;
use App\Http\Resources\RegistrationFeeTransResource;
use App\Http\Resources\TuitionFeeResource;
use App\Http\Resources\TuitionFeeTransacResource;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class FeePaymentController extends Controller
{
    //
    protected FeePaymentService $feePaymentService;
    public function __construct(FeePaymentService $feePaymentService)
    {
        $this->feePaymentService = $feePaymentService;
    }


    public function getTuitionFeeDetails(Request $request, $feeId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $tuitionFeeDetails = $this->feePaymentService->getTuitionFeeDetails($currentSchool, $feeId);
        return ApiResponseService::success("Tuition Fee Details Fetched Successfully", $tuitionFeeDetails, null, 200);
    }
    public function payTuitionFees(PayTuitionFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payFees = $this->feePaymentService->payStudentFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Fees Paid Sucessfully", $payFees, null, 201);
    }

    public function payRegistrationFees(PayRegistrationFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payRegistrationFees = $this->feePaymentService->payRegistrationFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Registration Fees Paid Sucessfully", $payRegistrationFees, null, 201);
    }

    public function getRegistrationFeeTransactionDetails(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $transactionDetails = $this->feePaymentService->getRegistrationFeeTransactionDetails($currentSchool, $transactionId);
        return ApiResponseService::success("Registration Fee Transaction Details Fetched Successfully", $transactionDetails, null, 200);
    }

    public function deleteRegistrationFeeTransaction(Request $request, $tranctionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->feePaymentService->deleteRegistrationFeeTransaction($currentSchool, $tranctionId);
        return ApiResponseService::success("Registration Fee Transaction Deleted Successfully", null, null, 200);
    }
    public function getRegistrationFees(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $registrationFees = $this->feePaymentService->getRegistrationFees($currentSchool);
        return ApiResponseService::success("Registration Fees Fetched Sucessfully", RegistrationFeeResource::collection($registrationFees), null, 200);
    }

    public function getFeesPaid(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $feePaid = $this->feePaymentService->getFeesPaid($currentSchool);
        return ApiResponseService::success('fee payment records fetched successfully', $feePaid, null, 200);
    }

    public function updateFeesPaid(UpdateTuitionFeePaymentRequest $request, $feeId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateFeesPaid = $this->feePaymentService->updateStudentFeesPayment($request->validated(), $feeId, $currentSchool);
        return ApiResponseService::success("Fee Payment Record Updated Sucessfully", $updateFeesPaid, null, 200);
    }

    public function deleteFeePaid(Request $request, $feeId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteFeePayment = $this->feePaymentService->deleteFeePayment($feeId, $currentSchool);
        return ApiResponseService::success('Record Deleted Sucessfully', $deleteFeePayment, null, 200);
    }

    public function getFeeDebtors(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $feeDebtors = $this->feePaymentService->getFeeDebtors($currentSchool);
        return ApiResponseService::success("Fee Debtors Fetched Succefully", FeeDebtorResource::collection($feeDebtors), null, 200);
    }

    public function getTuitionFees(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $tuitionFees = $this->feePaymentService->getTuitionFees($currentSchool);
        return ApiResponseService::success("Tuition Fees Fetched Successfully", TuitionFeeResource::collection($tuitionFees), null, 200);
    }

    public function getTuitionFeeTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $transactions = $this->feePaymentService->getTuitionFeeTransactions($currentSchool);
        return ApiResponseService::success("Tuition Fee Transactions fetched Successfully", TuitionFeeTransacResource::collection($transactions), null, 200);
    }

    public function reverseTuitionFeeTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $reverseTransaction = $this->feePaymentService->reverseFeePaymentTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Successfully", $reverseTransaction, null, 200);
    }

    public function deleteTuitionFeeTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->feePaymentService->deleteTuitionFeeTransaction($transactionId, $currentSchool);
    }

    public function getTuitionTransactionFeeDetails(Request $request, $tranctionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $transactionDetail = $this->feePaymentService->tuitionFeeTransactionDetails($tranctionId, $currentSchool);
        return ApiResponseService::success("Transaction Details Fetched Succesfully", $transactionDetail, null, 200);
    }

    public function getRegistrationFeeTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $registrationFeeTransactions = $this->feePaymentService->getRegistrationFeeTransactions($currentSchool);
        return ApiResponseService::success("Registration Fee Transactions Fetched Succesfully", RegistrationFeeTransResource::collection($registrationFeeTransactions), null, 200);
    }

    public function reverseRegistrationFeeTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $reverseTransaction = $this->feePaymentService->reverseRegistrationFeePaymentTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Sucessfully", $reverseTransaction, null, 200);
    }

    public function bulkReverseRegistrationFeeTransaction(RegistrationFeeTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $bulkReverseTransaction = $this->feePaymentService->bulkReverseRegistrationFeeTransaction($request->transactionIds, $currentSchool);
            return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkReverseTuitionFeeTransaction(TuitionFeeTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $bulkReverseTransaction = $this->feePaymentService->bulkReverseTuitionFeeTransaction($request->transactionIds, $currentSchool);
            return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkPayRegistrationFee(BulkPayRegistrationFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $bulkPayRegistrationFee = $this->feePaymentService->bulkPayRegistrationFee($request->registration_fee, $currentSchool);
            return ApiResponseService::success("Fee Paid Succesfully", $bulkPayRegistrationFee, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteTuitionFeeTransactions(TuitionFeeTransactionIdRequest $request)
    {

        try {
            $bulkDelete = $this->feePaymentService->bulkDeleteTuitionFeeTransaction($request->transactionIds);
            return ApiResponseService::success("Tuition Fee Transactions Deleted Successfully", $bulkDelete, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteRegistrationFeeTransactions(RegistrationFeeTransactionIdRequest $request)
    {
        try {
            $bulkDelete = $this->feePaymentService->bulkDeleteRegistrationFeeTransactions($request->transactionIds);
            return ApiResponseService::success("Transactions Deleted Succesfully", $bulkDelete, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteRegistrationFee(RegistrationFeeIdRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $this->feePaymentService->bulkDeleteRegistrationFee($request->registrationFeeIds, $currentSchool);
            return ApiResponseService::success("Registration Fee Deleted Successfully", null, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function deleteRegistrationFee(Request $request, $feeId)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $this->feePaymentService->deleteRegistrationFee($feeId, $currentSchool);
            return ApiResponseService::success("Registration Fee Deleted Successfully", null, null, 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getStudentRegistrationFee(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authStudent = $this->resolveUser();
        $registrationFees = $this->feePaymentService->getStudentRegistratonFees($currentSchool, $authStudent);
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
