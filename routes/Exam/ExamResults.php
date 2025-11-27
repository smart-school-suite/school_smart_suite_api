<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamResultController;

// Get all student results (potentially admin-only)
Route::middleware(['permission:schoolAdmin.examResults.view'])->get('/student-results', [ExamResultController::class, 'getAllStudentResults'])
    ->name('student-results.index');

// Get results for a specific student and exam ['permission:schoolAdmin.examResults.view.student|teacher.examResults.view.student|student.examResults.view.student']
Route::get('/exams/{examId}/students/{studentId}/results', [ExamResultController::class, 'getMyResults'])
    ->name('exams.students.results.show');

// Get standings for a specific exam
Route::middleware(['permission:schoolAdmin.examResults.view.standings'])->get('/exams/{examId}/standings', [ExamResultController::class, 'getStandingsByExam'])
    ->name('exams.standings.index');

// Generate PDF of standings for a specific exam
Route::middleware(['permission:schoolAdmin.examResults.view.standings'])->get('/exams/{examId}/standings/pdf', [ExamResultController::class, 'generateStudentResultStandingPdfByExam'])
    ->name('exams.standings.pdf');

// Generate PDF of results for a specific student and exam
Route::middleware(['permission:schoolAdmin.examResults.view.student|teacher.examResults.view.student|student.examResults.view.student'])->get('/exams/{examId}/students/{studentId}/results/pdf', [ExamResultController::class, 'generateStudentResultPdf'])
    ->name('exams.students.results.pdf');

Route::get('/result/{resultId}', [ExamResultController::class, 'getResultDetails'])->name('student.result.details');
