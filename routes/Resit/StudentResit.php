<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentResitController;

 // Get all resits for a specific student
 Route::middleware(['permission:student.studentResits.view.student|schoolAdmin.studentResits.view.student'])->get('/students/{studentId}/resits', [StudentResitController::class, 'getResitByStudent'])
 ->name('students.resits.index');

// Record payment for a resit middleware(['permission:schoolAdmin.studentResits.pay'])
Route::post('/pay-resit', [StudentResitController::class, 'payResit'])
 ->name('resit-payments.store');

// Update the status of a specific resit
Route::middleware(['permission:schoolAdmin.studentResits.update'])->put('/resits/{resitId}/status', [StudentResitController::class, 'update_exam_status'])
 ->name('resits.status.update');

// Update details of a specific resit
Route::middleware(['permission:schoolAdmin.studentResits.update'])->put('/resits/{resitId}', [StudentResitController::class, 'updateResit'])
 ->name('resits.update');

// Delete a specific resit
Route::middleware(['permission:schoolAdmin.studentResits.delete'])->delete('/resits/{resitId}', [StudentResitController::class, 'deleteResit'])
 ->name('resits.destroy');

// Get all student resits (potentially admin-only, consider prefixing with /admin)
Route::middleware(['permission:schoolAdmin.studentResits.view'])->get('/student-resits', [StudentResitController::class, 'getAllResits'])
 ->name('student-resits.index');

// Get details of a specific resit
Route::middleware(['permission:schoolAdmin.studentResits.show'])->get('/resits/{resitId}', [StudentResitController::class, 'getResitDetails'])
 ->name('resits.show');

// Get all resit payment transactions (potentially admin-only, consider prefixing with /admin)
Route::middleware(['permission:schoolAdmin.studentResits.transactions.view'])->get('/resit-transactions', [StudentResitController::class, 'getResitPaymentTransactions'])
 ->name('resit-transactions.index');

// Delete a specific resit payment transaction
Route::middleware(['permission:schoolAdmin.studentResits.transactions.delete'])->delete('/resit-transactions/{transactionId}', [StudentResitController::class, 'deleteFeePaymentTransaction'])
 ->name('resit-transactions.destroy');

// Get details of a specific resit payment transaction
Route::middleware(['permission:schoolAdmin.studentResits.transaction.show'])->get('/resit-transactions/{transactionId}', [StudentResitController::class, 'getTransactionDetails'])
 ->name('resit-transactions.show');

// Reverse a specific resit payment transaction
Route::middleware(['permission:schoolAdmin.studentResits.transaction.reverse'])->delete('/resit-transactions/{transactionId}/reverse', [StudentResitController::class, 'reverseTransaction'])
 ->name('resit-transactions.reverse');

// Submit resit results for a specific candidate
Route::middleware(['permission:schoolAdmin.studentResits.store.scores'])->post('/candidates/{candidateId}/resit-results', [StudentResitController::class, 'submitResitScores'])
 ->name('candidates.resit-results.store');

// Updating a specific student's resit scores
Route::middleware(['permission:schoolAdmin.studentResits.update.scores'])->put('/student-resits/{candidateId}', [StudentResitController::class, 'updateResitScores'])
->name('student-resits.update-scores');

// Bulk payment for student resits
Route::middleware(['permission:schoolAdmin.studentResits.pay'])->post('/student-resits/bulk-pay', [StudentResitController::class, 'bulkPayStudentResit'])
->name('student-resits.bulk-pay');

// Bulk deletion of student resits
Route::middleware(['permission:schoolAdmin.studentResits.delete'])->delete('/student-resits/bulk-delete', [StudentResitController::class, 'bulkDeleteStudentResit'])
->name('student-resits.bulk-delete');

// Bulk deletion of resit transactions
Route::middleware(['permission:schoolAdmin.studentResits.transactions.delete'])->post('/resit-transactions/bulk-delete', [StudentResitController::class, 'bulkDeleteStudentResitTransactions'])
->name('resit-transactions.bulk-delete');

// Bulk reversal of resit transactions
Route::middleware(['permission:schoolAdmin.studentResits.transaction.reverse'])->post('/resit-transactions/bulk-reverse', [StudentResitController::class, 'bulkReverseTransaction'])
->name('resit-transactions.bulk-reverse');

// Bulk update of student resits
Route::middleware(['permission:schoolAdmin.studentResits.update'])->put('/student-resits/bulk-update', [StudentResitController::class, 'bulkUpdateStudentResit'])
->name('student-resits.bulk-update');

// Fetching prepared resit evaluation data for a specific candidate and resit exam
Route::middleware(['permission:schoolAdmin.studentResits.view.evaluation.data'])->get('/resit-exams/{resitExamId}/candidates/{candidateId}/evaluation-data', [StudentResitController::class, 'getPreparedResitEvaluationData'])
->name('resit-exams.candidates.evaluation-data');

// Fetching eligible students for a specific resit exam
Route::middleware(['permission:schoolAdmin.studentResits.view.eligable.student'])->get('/resit-exams/{resitExamId}/eligible-students', [StudentResitController::class, 'getAllEligableStudentByExam'])
->name('resit-exams.eligible-students');

// Fetching eligible resit exams for a specific student
Route::middleware(['permission:schoolAdmin.studentResits.view.eligable.student.resitExam'])->get('/students/{studentId}/eligible-resit-exams', [StudentResitController::class, 'getEligableResitExamByStudent'])
->name('students.eligible-resit-exams');

Route::get('resit-scores/candidate/{candidateId}', [StudentResitController::class, "getResitScoresByCandidate"])->name("get.resit.scores.by.candidate");
