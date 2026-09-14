<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamScoreController;

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

Route::get('/exam-marks/exam-candidate/{candidateId}',[ExamScoreController::class, "getExamMarksByCandidate"])->name("get.exam.marks.by.exam_candidate");
Route::get('/ca-marks/exam-candidate/{candidateId}', [ExamScoreController::class, "getCaMarksByExamCandidate"])->name("get.ca.marks.by.exam-candidate");
