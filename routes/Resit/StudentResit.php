<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentResitController;

Route::middleware(['auth:sanctum'])->get('/get-student-resits/{student_id}', [StudentResitController::class, 'getResitByStudent']);
Route::middleware(['auth:sanctum'])->post('/pay-for-resit', [StudentResitController::class, 'payResit']);
Route::middleware(['auth:sanctum'])->put('/update-resit-status/{resit_id}', [StudentResitController::class, 'update_exam_status']);
Route::middleware(['auth:sanctum'])->put('/update-resit/{resit_id}', [StudentResitController::class, 'updateResit']);
Route::middleware(['auth:sanctum'])->delete('/delete-resit/{resit_id}', [StudentResitController::class, 'deleteResit']);
Route::middleware(['auth:sanctum'])->get('/student_resits', [StudentResitController::class, 'getAllResits']);
Route::middleware(['auth:sanctum'])->get("/details/{resit_id}", [StudentResitController::class, 'getResitDetails']);
Route::middleware(['auth:sanctum'])->get("/getTransactions", [StudentResitController::class, 'getResitPaymentTransactions']);
Route::middleware(['auth:sanctum'])->delete('/deleteTransaction/{transactionId}', [StudentResitController::class, 'deleteFeePaymentTransaction']);
Route::middleware(['auth:sanctum'])->get('/transactionDetails/{transactionId}', [StudentResitController::class, 'getTransactionDetails']);
Route::middleware(['auth:sanctum'])->delete('/reverseTransaction/{transactionId}', [StudentResitController::class, 'reverseTransaction']);
Route::middleware(['auth:sanctum'])->post('/submitResitResults', [StudentResitController::class, 'submitResitScores']);
Route::middleware(['auth:sanctum'])->put('/update-resit-scores/{candidateId}/{studentResitResultId}', [StudentResitController::class, 'updateResitScores']);
Route::middleware(['auth:sanctum'])->get("/getStudentResitData/{examId}/{studentId}", [StudentResitController::class, 'prepareResitData']);
Route::middleware(['auth:sanctum'])->post('/bulkPayStudentResit/{studentResitIds}', [StudentResitController::class, 'bulkPayStudentResit']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteStudentResit/{studentResitIds}', [StudentResitController::class, 'bulkDeleteStudentResit']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteResitTransactions/{studentResitIds}', [StudentResitController::class, 'bulkDeleteStudentResitTransactions']);
Route::middleware(['auth:santum'])->post('/bulkReverseResitTransactions/{transactionIds}', [StudentResitController::class, 'bulkReverseTransaction']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateStudentResit/{studentResitIds}', [StudentResitController::class, 'bulkUpdateStudentResit']);
Route::middleware(['auth:sanctum'])->put('/updateResitExams/{resitExamId}', [StudentResitController::class, 'updateResitExams']);
Route::middleware(['auth:sanctum'])->get('/getPreparedResitEvaluationData/{resitExamId}/{candidateId}', [StudentResitController::class, 'getPreparedResitEvaluationData']);
Route::middleware(['auth:sanctum'])->get('/getAllResitExams', [StudentResitController::class, 'getAllResitExams']);
Route::middleware(['auth:sanctum'])->get('/getEligableStudentsByExam/{resitExamId}', [StudentResitController::class, 'getAllEligableStudentByExam']);
Route::middleware(['auth:sanctum'])->get('/getResitExamsByStudent/{studentId}', [StudentResitController::class, 'getEligableResitExamByStudent']);
