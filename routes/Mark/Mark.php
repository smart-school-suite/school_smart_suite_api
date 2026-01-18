<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamScoreController;


Route::post('/ca-scores', [ExamScoreController::class, 'createCaMark'])
    ->name('ca-scores.store');

Route::post('/exam-scores', [ExamScoreController::class, 'createExamMark'])
    ->name('exam-scores.store');

Route::put('/ca-scores', [ExamScoreController::class, 'updateCaMark'])
    ->name('ca-scores.update');

Route::put('/exam-scores', [ExamScoreController::class, 'updateExamMark'])
    ->name('exam-scores.update');

Route::get('/students/{studentId}/exams/{examId}/scores', [ExamScoreController::class, 'getMarksByExamStudent'])
    ->name('students.exams.scores.show');


Route::get('/exams/{examId}/accessed-courses', [ExamScoreController::class, 'getAccessedCoursesWithLettergrades'])
    ->name('exams.accessed-courses.index');

Route::get('/exams/{examId}/students/{studentId}/ca-results/prepare', [ExamScoreController::class, 'prepareCaResultsByExam'])
    ->name('exams.students.ca-results.prepare');

Route::get('/exams/{examId}/students/{studentId}/ca-data/prepare', [ExamScoreController::class, 'prepareCaData'])
    ->name('exams.students.ca-data.prepare');

Route::get('/exams/{examId}/students/{studentId}/exam-data/prepare', [ExamScoreController::class, 'prepareExamData'])
    ->name('exams.students.exam-data.prepare');

Route::get('/ca-helper-data/{examId}', [ExamScoreController::class, 'getCaEvaluationHelperData'])->name("ca-helper-data");

Route::get('/exam-helper-data/{examId}/{studentId}', [ExamScoreController::class, 'getExamEvaluationHelperData'])->name('exam-helper-data');

Route::get('/exam-marks/exam-candidate/{candidateId}',[ExamScoreController::class, "getExamMarksByCandidate"])->name("get.exam.marks.by.exam_candidate");
Route::get('/ca-marks/exam-candidate/{candidateId}', [ExamScoreController::class, "getCaMarksByExamCandidate"])->name("get.ca.marks.by.exam-candidate");
