<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateResitDatesRequest;
use Exception;
use Illuminate\Http\Request;
use App\Services\StudentResitService;
use App\Services\ResitScoresService;
use App\Http\Requests\ResitPaymentRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\resitScoreRequest;
use App\Http\Requests\BulkUpdateStudentResitRequest;
use App\Http\Requests\UpdateResitExamRequest;
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
       $this->resitScoresService->submitStudentResitScores($request->entries, $currentSchool, $candidateId);
        return ApiResponseService::success("Resit Scores Submitted Successfully", null, null, 200);
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
    public function bulkDeleteStudentResit($studentResitIds){
        $idsArray = explode(',', $studentResitIds);
        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student_resit,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
           $bulkDeleteStudentResit = $this->studentResitService->bulkDeleteStudentResit($idsArray);
           return ApiResponseService::success("Student Resit Deleted Succesfully", $bulkDeleteStudentResit, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkPayStudentResit(ResitPaymentRequest $request, $studentResitIds){
        $currentSchool = $request->attributes->get("currentSchool");
        $idsArray = explode(',', $studentResitIds);
        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student_resit,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
            $bulkPayStudentResit = $this->studentResitService->bulkPayStudentResit($request->validated(), $idsArray, $currentSchool);
            return ApiResponseService::success("Student Resit Paid Succesfully", $bulkPayStudentResit, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteStudentResitTransactions($transactionIds){
        $idsArray = explode(',', $transactionIds);
        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:resit_fee_transactions,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
            $bulkDeletResitTransactions = $this->studentResitService->bulkDeleteTransaction($idsArray);
            return ApiResponseService::success("Student Resit Transactions Deleted Succefully", $bulkDeletResitTransactions, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkReverseTransaction(Request $request, $transactionIds){
        $currentSchool = $request->attributes->get("currentSchool");
        $idsArray = explode(',', $transactionIds);
        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:resit_fee_transactions,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
             $bulkReverseTransaction = $this->studentResitService->bulkReverseResitTransaction($idsArray, $currentSchool);
             return ApiResponseService::success("Transactions Reversed Succesfully", $bulkReverseTransaction, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkUpdateStudentResit(BulkUpdateStudentResitRequest $request){
       try{
           $bulkUpdateStudentResit = $this->studentResitService->bulkUpdateStudentResit($request->validated);
           return ApiResponseService::success("Student Resit Updated Successfully", $bulkUpdateStudentResit, null, 200);
       }
       catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
       }
    }
    public function updateResitExams(UpdateResitExamRequest $request, $resitExamId){
        $currentSchool = $request->attributes->get("currentSchool");
        $updateDates = $this->studentResitService->updateResitExam($request->all(), $currentSchool, $resitExamId);
        return ApiResponseService::success("Resit Exam Dates Set Successfully", $updateDates, null, 200);
    }
    public function getPreparedResitEvaluationData(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $resitExamId = $request->route('resitExamId');
        $candidateId = $request->route('candidateId');
        $resitData = $this->studentResitService->prepareEvaluationData($candidateId, $resitExamId, $currentSchool);
        return ApiResponseService::success("Resit Evaluation Data Fetched Successfully", $resitData, null, 200);
    }
    public function getAllResitExams(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $resitExams = $this->studentResitService->getAllResitExams($currentSchool);
        return ApiResponseService::success("Resit Exams Fetched Successfully", $resitExams, null, 200);
    }
    public function getAllEligableStudentByExam(Request $request, $resitExamId){
        $currentSchool = $request->attributes->get("currentSchool");
        $eligableStudents = $this->studentResitService->getAllEligableStudents($currentSchool, $resitExamId);
        return ApiResponseService::success("Eligable Students Fetched Successfully", $eligableStudents, null, 200);
    }
    public function getEligableResitExamByStudent(Request $request, $studentId){
        $currentSchool = $request->attributes->get("currentSchool");
        $getResitExams = $this->studentResitService->getEligableResitExamByStudent($currentSchool, $studentId);
        return ApiResponseService::success("Resit Exams Fetched Successfully", $getResitExams, null, 200);
    }

}
