<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Resit\ResitController;
use App\Http\Controllers\ResitEvaluation\ResitEvaluationController;

 // Get all resits for a specific student
 Route::middleware(['permission:student.studentResits.view.student|schoolAdmin.studentResits.view.student'])->get('/students/{studentId}/resits', [ResitController::class, 'getResitByStudent'])
 ->name('students.resits.index');


// Update the status of a specific resit
Route::middleware(['permission:schoolAdmin.studentResits.update'])->put('/resits/{resitId}/status', [ResitController::class, 'update_exam_status'])
 ->name('resits.status.update');

// Update details of a specific resit
Route::middleware(['permission:schoolAdmin.studentResits.update'])->put('/resits/{resitId}', [ResitController::class, 'updateResit'])
 ->name('resits.update');

// Delete a specific resit
Route::middleware(['permission:schoolAdmin.studentResits.delete'])->delete('/resits/{resitId}', [ResitController::class, 'deleteResit'])
 ->name('resits.destroy');

// Get all student resits (potentially admin-only, consider prefixing with /admin)
Route::middleware(['permission:schoolAdmin.studentResits.view'])->get('/student-resits', [ResitController::class, 'getAllResits'])
 ->name('student-resits.index');

// Get details of a specific resit
Route::middleware(['permission:schoolAdmin.studentResits.show'])->get('/resits/{resitId}', [ResitController::class, 'getResitDetails'])
 ->name('resits.show');

// Submit resit results for a specific candidate
Route::middleware(['permission:schoolAdmin.studentResits.store.scores'])->post('/candidates/{candidateId}/resit-results', [ResitEvaluationController::class, 'submitResitScores'])
 ->name('candidates.resit-results.store');

// Updating a specific student's resit scores
Route::middleware(['permission:schoolAdmin.studentResits.update.scores'])->put('/student-resits/{candidateId}', [ResitEvaluationController::class, 'updateResitScores'])
->name('student-resits.update-scores');

// Bulk deletion of student resits
Route::middleware(['permission:schoolAdmin.studentResits.delete'])->delete('/student-resits/bulk-delete', [ResitController::class, 'bulkDeleteStudentResit'])
->name('student-resits.bulk-delete');

// Bulk update of student resits
Route::middleware(['permission:schoolAdmin.studentResits.update'])->put('/student-resits/bulk-update', [ResitController::class, 'bulkUpdateStudentResit'])
->name('student-resits.bulk-update');

// Fetching prepared resit evaluation data for a specific candidate and resit exam
Route::middleware(['permission:schoolAdmin.studentResits.view.evaluation.data'])->get('/resit-exams/{resitExamId}/candidates/{candidateId}/evaluation-data', [ResitController::class, 'getPreparedResitEvaluationData'])
->name('resit-exams.candidates.evaluation-data');

// Fetching eligible students for a specific resit exam
Route::middleware(['permission:schoolAdmin.studentResits.view.eligable.student'])->get('/resit-exams/{resitExamId}/eligible-students', [ResitController::class, 'getAllEligableStudentByExam'])
->name('resit-exams.eligible-students');

// Fetching eligible resit exams for a specific student
Route::middleware(['permission:schoolAdmin.studentResits.view.eligable.student.resitExam'])->get('/students/{studentId}/eligible-resit-exams', [ResitController::class, 'getEligableResitExamByStudent'])
->name('students.eligible-resit-exams');

Route::get('resit-scores/candidate/{candidateId}', [ResitController::class, "getResitScoresByCandidate"])->name("get.resit.scores.by.candidate");

Route::get("student/{studentId}/resits", [ResitController::class, "getResitStudentId"])->name("get.student.resits");
Route::get("student/{studentId}/semester/{semesterId}/resits", [ResitController::class, "getResitStudentIdSemesterId"])->name("get.student.resit.by.semester");
Route::get("/student/{studentId}/carry-overs", [ResitController::class, "getResitStudentIdCarryOver"])->name("get.student.carryovers");
