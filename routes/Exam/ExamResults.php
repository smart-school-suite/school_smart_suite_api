<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentResultController;

// Get all student results (potentially admin-only)
Route::get('/student-results', [StudentResultController::class, 'getAllStudentResults'])
    ->name('student-results.index');

// Get results for a specific student and exam
Route::get('/exams/{examId}/students/{studentId}/results', [StudentResultController::class, 'getMyResults'])
    ->name('exams.students.results.show');

// Get standings for a specific exam
Route::get('/exams/{examId}/standings', [StudentResultController::class, 'getStandingsByExam'])
    ->name('exams.standings.index');

// Generate PDF of standings for a specific exam
Route::get('/exams/{examId}/standings/pdf', [StudentResultController::class, 'generateStudentResultStandingPdfByExam'])
    ->name('exams.standings.pdf');

// Generate PDF of results for a specific student and exam
Route::get('/exams/{examId}/students/{studentId}/results/pdf', [StudentResultController::class, 'generateStudentResultPdf'])
    ->name('exams.students.results.pdf');
