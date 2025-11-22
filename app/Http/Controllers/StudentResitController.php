<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentResit\StudentResitIdRequest;
use App\Http\Requests\StudentResit\StudentResitTransactionIdRequest;
use App\Http\Resources\ResitResource;
use Exception;
use Illuminate\Http\Request;
use App\Services\StudentResitService;
use App\Services\ResitScoresService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StudentResit\BulkUpdateStudentResitRequest;
use App\Http\Requests\ResitExamScore\CreateResitExamScore;
use App\Http\Requests\ResitExamScore\UpdateResitExamScore;
use App\Http\Requests\StudentResit\BulkPayStudentResitRequest;
use App\Http\Requests\StudentResit\PayResitFeeRequest;
use App\Http\Requests\TuitionFee\BulkPayTuitionFeeRequest;
use App\Http\Resources\StudentResitTransResource;
use App\Services\UpdateResitScoreService;
use App\Services\ApiResponseService;

class StudentResitController extends Controller
{
    //
    protected StudentResitService $studentResitService;

    protected ResitScoresService $resitScoresService;

    protected UpdateResitScoreService $updateResitScoreService;

    public function __construct(
        StudentResitService $studentResitService,
        ResitScoresService $resitScoresService,
        UpdateResitScoreService $updateResitScoreService
    ) {
        $this->resitScoresService = $resitScoresService;
        $this->studentResitService = $studentResitService;
        $this->updateResitScoreService = $updateResitScoreService;
    }
    public function submitResitScores(CreateResitExamScore $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $candidateId = $request->route('candidateId');
        $resitScores = $this->resitScoresService->submitStudentResitScores($request->entries, $currentSchool, $candidateId);
        return ApiResponseService::success("Resit Scores Submitted Successfully", $resitScores, null, 200);
    }
    public function updateResitScores(UpdateResitExamScore $request)
    {
        try {
            $candidateId = $request->route('candidateId');
            $currentSchool = $request->attributes->get('currentSchool');
            $updateResitScores = $this->updateResitScoreService->updateResitScores(
                $request->entries,
                $currentSchool,
                $candidateId
            );
            return ApiResponseService::success("Student Resit Scores Updated Successfully", $updateResitScores, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 404);
        }
    }
    public function updateResit(Request $request, $resitId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudentResit = $this->studentResitService->updateStudentResit($request->all(), $currentSchool, $resitId);
        return ApiResponseService::success("Resit Entry Updated Successfully", $updateStudentResit, null, 200);
    }
    public function payResit(PayResitFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payStudentResit = $this->studentResitService->payResit($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Resit Paid Successfully", $payStudentResit, null, 200);
    }
    public function deleteResit(Request $request, $resitId)
    {
        $resitId = $request->route('resitId');
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudentResit = $this->studentResitService->deleteStudentResit($resitId, $currentSchool);
        return ApiResponseService::success("Student Resit Record Not Found", $deleteStudentResit, null, 200);
    }
    public function getResitByStudent(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $studentId = $request->route('studentId');
        $getMyResits = $this->studentResitService->getMyResits($currentSchool, $studentId);
        return ApiResponseService::success("Student Records Fetched Sucessfully", $getMyResits, null, 200);
    }
    public function getAllResits(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentResits = $this->studentResitService->getResitableCourses($currentSchool);
        return ApiResponseService::success("Student Resit Records Fetched Sucessfully", ResitResource::collection($getStudentResits), null, 200);
    }
    public function getResitDetails(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $resitId = $request->route("resitId");
        $getStudentResitDetails = $this->studentResitService->getStudentResitDetails($currentSchool, $resitId);
        return ApiResponseService::success("Student Resit Details Fetched Successfully", $getStudentResitDetails, null, 200);
    }
    public function getResitPaymentTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getResitTransactions = $this->studentResitService->getResitPaymentTransactions($currentSchool);
        return ApiResponseService::success("Student Resit Payment Transactions Fetched Succefully", StudentResitTransResource::collection($getResitTransactions), null, 200);
    }
    public function deleteFeePaymentTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteTransaction = $this->studentResitService->deleteResitFeeTransaction($currentSchool, $transactionId);
        return ApiResponseService::success("Transaction Deleted Succesfully", $deleteTransaction, null, 200);
    }
    public function getTransactionDetails(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $transactionDetails = $this->studentResitService->getTransactionDetails($currentSchool, $transactionId);
        return ApiResponseService::success("Transaction Details Fetched Succesfully", $transactionDetails, null, 200);
    }
    public function reverseTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $reverseTransaction = $this->studentResitService->reverseResitTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Succesfully", $reverseTransaction, null, 200);
    }
    public function getPreparedResitEvaluationData(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $resitExamId = $request->route('resitExamId');
        $candidateId = $request->route('candidateId');
        $prepareResitData = $this->studentResitService->getResitEvaluationHelperData($currentSchool, $resitExamId, $candidateId);
        return ApiResponseService::success("Resit Evaluation Helper Data Fetched Successfully", $prepareResitData, null, 200);
    }

    public function getResitScoresByCandidate(Request $request)
    {
        try {
            $candidateId = $request->route('candidateId');
            $currentSchool = $request->attributes->get("currentSchool");
            $resitScores = $this->studentResitService->getResitScoresByCandidate($currentSchool, $candidateId);
            return ApiResponseService::success("Resit Scores Fetched Successfully", $resitScores, null, 200);
        } catch (Exception $e) {
            return  ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteStudentResit(StudentResitIdRequest $request)
    {
        try {
            $bulkDeleteStudentResit = $this->studentResitService->bulkDeleteStudentResit($request->resitIds);
            return ApiResponseService::success("Student Resit Deleted Succesfully", $bulkDeleteStudentResit, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkPayStudentResit(BulkPayStudentResitRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get("currentSchool");
            $bulkPayStudentResit = $this->studentResitService->bulkPayStudentResit($request->paymentData, $currentSchool);
            return ApiResponseService::success("Student Resit Paid Succesfully", $bulkPayStudentResit, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteStudentResitTransactions(StudentResitTransactionIdRequest $request)
    {
        try {
            $bulkDeletResitTransactions = $this->studentResitService->bulkDeleteTransaction($request->transactionIds);
            return ApiResponseService::success("Student Resit Transactions Deleted Succefully", $bulkDeletResitTransactions, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkReverseTransaction(StudentResitTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        try {
            $bulkReverseTransaction = $this->studentResitService->bulkReverseResitTransaction($request->transactionIds, $currentSchool);
            return ApiResponseService::success("Transactions Reversed Succesfully", $bulkReverseTransaction, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkUpdateStudentResit(BulkUpdateStudentResitRequest $request)
    {
        try {
            $bulkUpdateStudentResit = $this->studentResitService->bulkUpdateStudentResit($request->validated);
            return ApiResponseService::success("Student Resit Updated Successfully", $bulkUpdateStudentResit, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function getAllEligableStudentByExam(Request $request, $resitExamId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $eligableStudents = $this->studentResitService->getAllEligableStudents($currentSchool, $resitExamId);
        return ApiResponseService::success("Eligable Students Fetched Successfully", $eligableStudents, null, 200);
    }
    public function getEligableResitExamByStudent(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getResitExams = $this->studentResitService->getEligableResitExamByStudent($currentSchool, $studentId);
        return ApiResponseService::success("Resit Exams Fetched Successfully", $getResitExams, null, 200);
    }

    public function getResitStudentId(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentResits = $this->studentResitService->getResitStudentId($currentSchool, $studentId);
        return ApiResponseService::success("Student Resits Fetched Successfully", $studentResits, null, 200);
    }

    public function getResitStudentIdSemesterId(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentId = $request->route("studentId");
        $semesterId = $request->route("semesterId");
        $studentResits = $this->studentResitService->getResitStudentIdSemesterId($currentSchool, $studentId, $semesterId);
        return ApiResponseService::success("Student Resits Fetched Successfully", $studentResits, null, 200);
    }

    public function getResitStudentIdCarryOver(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentResits = $this->studentResitService->getResitStudentIdCarryOver($currentSchool, $studentId);
        return ApiResponseService::success("Student Carry Overs Fetched Succesfully", $studentResits, null, 200);
    }
}
