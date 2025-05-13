<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentResitController;

 // Get all resits for a specific student
 Route::get('/students/{studentId}/resits', [StudentResitController::class, 'getResitByStudent'])
 ->name('students.resits.index');

// Record payment for a resit
Route::post('/resit-payments', [StudentResitController::class, 'payResit'])
 ->name('resit-payments.store');

// Update the status of a specific resit
Route::put('/resits/{resitId}/status', [StudentResitController::class, 'update_exam_status'])
 ->name('resits.status.update');

// Update details of a specific resit
Route::put('/resits/{resitId}', [StudentResitController::class, 'updateResit'])
 ->name('resits.update');

// Delete a specific resit
Route::delete('/resits/{resitId}', [StudentResitController::class, 'deleteResit'])
 ->name('resits.destroy');

// Get all student resits (potentially admin-only, consider prefixing with /admin)
Route::get('/student-resits', [StudentResitController::class, 'getAllResits'])
 ->name('student-resits.index');

// Get details of a specific resit
Route::get('/resits/{resitId}', [StudentResitController::class, 'getResitDetails'])
 ->name('resits.show');

// Get all resit payment transactions (potentially admin-only, consider prefixing with /admin)
Route::get('/resit-transactions', [StudentResitController::class, 'getResitPaymentTransactions'])
 ->name('resit-transactions.index');

// Delete a specific resit payment transaction
Route::delete('/resit-transactions/{transactionId}', [StudentResitController::class, 'deleteFeePaymentTransaction'])
 ->name('resit-transactions.destroy');

// Get details of a specific resit payment transaction
Route::get('/resit-transactions/{transactionId}', [StudentResitController::class, 'getTransactionDetails'])
 ->name('resit-transactions.show');

// Reverse a specific resit payment transaction
Route::delete('/resit-transactions/{transactionId}/reverse', [StudentResitController::class, 'reverseTransaction'])
 ->name('resit-transactions.reverse');

// Submit resit results for a specific candidate
Route::post('/candidates/{candidateId}/resit-results', [StudentResitController::class, 'submitResitScores'])
 ->name('candidates.resit-results.store');

// Updating a specific student's resit scores
Route::put('/student-resits/{candidateId}/{studentResitResultId}', [StudentResitController::class, 'updateResitScores'])
->name('student-resits.update-scores');

// Fetching resit data for a specific student and exam
Route::get('/student-resits/{examId}/{studentId}/data', [StudentResitController::class, 'prepareResitData'])
->name('student-resits.data');

// Bulk payment for student resits
Route::post('/student-resits/bulk-pay', [StudentResitController::class, 'bulkPayStudentResit'])
->name('student-resits.bulk-pay');

// Bulk deletion of student resits
Route::delete('/student-resits/bulk-delete/{studentResitIds}', [StudentResitController::class, 'bulkDeleteStudentResit'])
->name('student-resits.bulk-delete');

// Bulk deletion of resit transactions
Route::delete('/resit-transactions/bulk-delete/{transactionIds}', [StudentResitController::class, 'bulkDeleteStudentResitTransactions'])
->name('resit-transactions.bulk-delete');

// Bulk reversal of resit transactions
Route::post('/resit-transactions/bulk-reverse/{transactionIds}', [StudentResitController::class, 'bulkReverseTransaction'])
->name('resit-transactions.bulk-reverse');

// Bulk update of student resits
Route::put('/student-resits/bulk-update', [StudentResitController::class, 'bulkUpdateStudentResit'])
->name('student-resits.bulk-update');

// Fetching prepared resit evaluation data for a specific candidate and resit exam
Route::get('/resit-exams/{resitExamId}/candidates/{candidateId}/evaluation-data', [StudentResitController::class, 'getPreparedResitEvaluationData'])
->name('resit-exams.candidates.evaluation-data');

// Fetching eligible students for a specific resit exam
Route::get('/resit-exams/{resitExamId}/eligible-students', [StudentResitController::class, 'getAllEligableStudentByExam'])
->name('resit-exams.eligible-students');

// Fetching eligible resit exams for a specific student
Route::get('/students/{studentId}/eligible-resit-exams', [StudentResitController::class, 'getEligableResitExamByStudent'])
->name('students.eligible-resit-exams');
