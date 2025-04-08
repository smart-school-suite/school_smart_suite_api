<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StudentResitService;
use App\Services\ResitScoresService;
use App\Http\Requests\ResitPaymentRequest;
use App\Http\Requests\resitScoreRequest;
use App\Services\ApiResponseService;

class StudentResitController extends Controller
{
    //
    protected StudentResitService $studentResitService;

    protected ResitScoresService $resitScoresService;

    public function __construct(StudentResitService $studentResitService, ResitScoresService $resitScoresService)
    {
        $this->resitScoresService = $resitScoresService;
        $this->studentResitService = $studentResitService;
    }

    public function submitResitScores(resitScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $candidateId = $request->route('candidateId');
        $resitScores = $this->resitScoresService->submitStudentResitScores($request->entries, $currentSchool, $candidateId);
        return ApiResponseService::success("Resit Scores Submitted Successfully", $resitScores, null, 200);
    }
    public function updateResit(Request $request, $resit_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudentResit = $this->studentResitService->updateStudentResit($request->all(), $currentSchool, $resit_id);
        return ApiResponseService::success("Resit Entry Updated Successfully", $updateStudentResit, null, 200);
    }

    public function payResit(ResitPaymentRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payStudentResit = $this->studentResitService->payResit($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Resit Paid Successfully", $payStudentResit, null, 200);
    }

    public function deleteResit(Request $request, $resit_id)
    {
        $resit_id = $request->route('resit_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudentResit = $this->studentResitService->deleteStudentResit($resit_id, $currentSchool);
        return ApiResponseService::success("Student Resit Record Not Found", $deleteStudentResit, null, 200);
    }

    public function getResitByStudent(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_id = $request->route('student_id');
        $exam_id = $request->route("exam_id");
        $getMyResits = $this->studentResitService->getMyResits($currentSchool, $student_id, $exam_id);
        return ApiResponseService::success("Student Records Fetched Sucessfully", $getMyResits, null, 200);
    }

    public function getAllResits(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentResits = $this->studentResitService->getStudentResits($currentSchool);
        return ApiResponseService::success("Student Resit Records Fetched Sucessfully", $getStudentResits, null, 200);
    }

    public function getResitDetails(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $resit_id = $request->route("resit_id");
        $getStudentResitDetails = $this->studentResitService->getStudentResitDetails($currentSchool, $resit_id);
        return ApiResponseService::success("Student Resit Details Fetched Successfully", $getStudentResitDetails, null, 200);
    }

    public function getResitPaymentTransactions(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $getResitTransactions = $this->studentResitService->getResitPaymentTransactions($currentSchool);
        return ApiResponseService::success("Student Resit Payment Transactions Fetched Succefully", $getResitTransactions, null, 200);
    }

    public function deleteFeePaymentTransaction(Request $request, $transactionId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteTransaction = $this->studentResitService->deleteResitFeeTransaction($currentSchool, $transactionId);
        return ApiResponseService::success("Transaction Deleted Succesfully", $deleteTransaction, null, 200);
    }

    public function getTransactionDetails(Request $request, $transactionId){
        $currentSchool = $request->attributes->get("currentSchool");
        $transactionDetails = $this->studentResitService->getTransactionDetails($currentSchool, $transactionId);
        return ApiResponseService::success("Transaction Details Fetched Succesfully", $transactionDetails, null, 200);
    }

    public function reverseTransaction(Request $request, $transactionId){
        $currentSchool = $request->attributes->get("currentSchool");
        $reverseTransaction = $this->studentResitService->reverseResitTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Succesfully", $reverseTransaction, null, 200);
    }

    public function prepareResitData(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route('examId');
        $studentId = $request->route('studentId');
        $prepareResitData = $this->studentResitService->prepareResitScoresData($currentSchool, $examId, $studentId);
        return ApiResponseService::success("Student Resit Info fetched Succesfully", $prepareResitData, null, 200);
    }
}
