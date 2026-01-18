<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamResultController;


Route::get('/student-results', [ExamResultController::class, 'getAllStudentResults'])
    ->name('student-results.index');

Route::get('/exams/{examId}/students/{studentId}/results', [ExamResultController::class, 'getMyResults'])
    ->name('exams.students.results.show');

Route::get('/exams/{examId}/standings', [ExamResultController::class, 'getStandingsByExam'])
    ->name('exams.standings.index');

Route::get('/exams/{examId}/standings/pdf', [ExamResultController::class, 'generateStudentResultStandingPdfByExam'])
    ->name('exams.standings.pdf');

Route::get('/exams/{examId}/students/{studentId}/results/pdf', [ExamResultController::class, 'generateStudentResultPdf'])
    ->name('exams.students.results.pdf');

Route::get('/result/{resultId}', [ExamResultController::class, 'getResultDetails'])->name('student.result.details');
