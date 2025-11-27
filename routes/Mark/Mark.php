<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarksController;
use App\Http\Controllers\Exam\ExamScoreController;
// Add CA scores for a student in a course
Route::middleware(['permission:schoolAdmin.mark.create.ca.marks'])->post('/ca-scores', [ExamScoreController::class, 'createCaMark'])
    ->name('ca-scores.store');

// Add exam scores for a student in a course
Route::middleware(['permission:schoolAdmin.mark.create.exam.marks'])->post('/exam-scores', [ExamScoreController::class, 'createExamMark'])
    ->name('exam-scores.store');

// Update CA score for a student in a course
Route::middleware(['permission:schoolAdmin.mark.update.ca.marks'])->put('/ca-scores', [ExamScoreController::class, 'updateCaMark'])
    ->name('ca-scores.update');

// Update exam score for a student in a course
Route::middleware(['permission:schoolAdmin.mark.update.exam.marks'])->put('/exam-scores', [ExamScoreController::class, 'updateExamMark'])
    ->name('exam-scores.update');

// Get scores for a specific student in a specific exam
Route::middleware(['permission:schoolAdmin.mark.view.student'])->get('/students/{studentId}/exams/{examId}/scores', [ExamScoreController::class, 'getMarksByExamStudent'])
    ->name('students.exams.scores.show');


// Get courses accessed within a specific exam (with letter grades)
Route::middleware(['permission:schoolAdmin.mark.view.accessed.courses'])->get('/exams/{examId}/accessed-courses', [ExamScoreController::class, 'getAccessedCoursesWithLettergrades'])
    ->name('exams.accessed-courses.index');

// Prepare CA results data for a specific student in a specific exam
Route::middleware(['permission:schoolAdmin.mark.view.ca.result.data'])->get('/exams/{examId}/students/{studentId}/ca-results/prepare', [ExamScoreController::class, 'prepareCaResultsByExam'])
    ->name('exams.students.ca-results.prepare');

// Prepare CA data for a specific student in a specific exam
Route::middleware(['permission:schoolAdmin.mark.view.ca.evaluation.data'])->get('/exams/{examId}/students/{studentId}/ca-data/prepare', [ExamScoreController::class, 'prepareCaData'])
    ->name('exams.students.ca-data.prepare');

// Prepare exam data for a specific student in a specific exam
Route::middleware(['permission:schoolAdmin.mark.view.exam.evaluation.data'])->get('/exams/{examId}/students/{studentId}/exam-data/prepare', [ExamScoreController::class, 'prepareExamData'])
    ->name('exams.students.exam-data.prepare');

Route::get('/ca-helper-data/{examId}', [ExamScoreController::class, 'getCaEvaluationHelperData'])->name("ca-helper-data");

Route::get('/exam-helper-data/{examId}/{studentId}', [ExamScoreController::class, 'getExamEvaluationHelperData'])->name('exam-helper-data');

Route::get('/exam-marks/exam-candidate/{candidateId}',[ExamScoreController::class, "getExamMarksByCandidate"])->name("get.exam.marks.by.exam_candidate");
Route::get('/ca-marks/exam-candidate/{candidateId}', [ExamScoreController::class, "getCaMarksByExamCandidate"])->name("get.ca.marks.by.exam-candidate");
