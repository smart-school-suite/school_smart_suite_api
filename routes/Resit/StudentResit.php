<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Resit\ResitController;
use App\Http\Controllers\ResitEvaluation\ResitEvaluationController;

 Route::get('/students/{studentId}/resits', [ResitController::class, 'getResitByStudent'])
 ->name('students.resits.index');

Route::put('/resits/{resitId}/status', [ResitController::class, 'update_exam_status'])
 ->name('resits.status.update');

Route::put('/resits/{resitId}', [ResitController::class, 'updateResit'])
 ->name('resits.update');

Route::delete('/resits/{resitId}', [ResitController::class, 'deleteResit'])
 ->name('resits.destroy');

Route::get('/student-resits', [ResitController::class, 'getAllResits'])
 ->name('student-resits.index');

Route::get('/resits/{resitId}', [ResitController::class, 'getResitDetails'])
 ->name('resits.show');

Route::post('/candidates/{candidateId}/resit-results', [ResitEvaluationController::class, 'submitResitScores'])
 ->name('candidates.resit-results.store');

Route::put('/student-resits/{candidateId}', [ResitEvaluationController::class, 'updateResitScores'])
->name('student-resits.update-scores');

Route::delete('/student-resits/bulk-delete', [ResitController::class, 'bulkDeleteStudentResit'])
->name('student-resits.bulk-delete');


Route::put('/student-resits/bulk-update', [ResitController::class, 'bulkUpdateStudentResit'])
->name('student-resits.bulk-update');


Route::get('/resit-exams/{resitExamId}/candidates/{candidateId}/evaluation-data', [ResitController::class, 'getPreparedResitEvaluationData'])
->name('resit-exams.candidates.evaluation-data');

Route::get('/resit-exams/{resitExamId}/eligible-students', [ResitController::class, 'getAllEligableStudentByExam'])
->name('resit-exams.eligible-students');


Route::get('/students/{studentId}/eligible-resit-exams', [ResitController::class, 'getEligableResitExamByStudent'])
->name('students.eligible-resit-exams');

Route::get('resit-scores/candidate/{candidateId}', [ResitController::class, "getResitScoresByCandidate"])->name("get.resit.scores.by.candidate");

Route::get("student/{studentId}/resits", [ResitController::class, "getResitStudentId"])->name("get.student.resits");
Route::get("student/{studentId}/semester/{semesterId}/resits", [ResitController::class, "getResitStudentIdSemesterId"])->name("get.student.resit.by.semester");
Route::get("/student/{studentId}/carry-overs", [ResitController::class, "getResitStudentIdCarryOver"])->name("get.student.carryovers");
