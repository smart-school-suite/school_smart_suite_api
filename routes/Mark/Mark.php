<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarksController;

// Add CA scores for a student in a course
Route::middleware(['permission:schoolAdmin.mark.create.ca.marks'])->post('/ca-scores', [MarksController::class, 'createCaMark'])
    ->name('ca-scores.store');

// Add exam scores for a student in a course
Route::middleware(['permission:schoolAdmin.mark.create.exam.marks'])->post('/exam-scores', [MarksController::class, 'createExamMark'])
    ->name('exam-scores.store');

// Update CA score for a student in a course
Route::middleware(['permission:schoolAdmin.mark.update.ca.marks'])->put('/ca-scores', [MarksController::class, 'updateCaMark'])
    ->name('ca-scores.update');

// Update exam score for a student in a course
Route::middleware(['permission:schoolAdmin.mark.update.exam.marks'])->put('/exam-scores', [MarksController::class, 'updateExamMark'])
    ->name('exam-scores.update');

// Get scores for a specific student in a specific exam
Route::middleware(['permission:schoolAdmin.mark.view.student'])->get('/students/{studentId}/exams/{examId}/scores', [MarksController::class, 'getMarksByExamStudent'])
    ->name('students.exams.scores.show');


// Get courses accessed within a specific exam (with letter grades)
Route::middleware(['permission:schoolAdmin.mark.view.accessed.courses'])->get('/exams/{examId}/accessed-courses', [MarksController::class, 'getAccessedCoursesWithLettergrades'])
    ->name('exams.accessed-courses.index');

// Prepare CA results data for a specific student in a specific exam
Route::middleware(['permission:schoolAdmin.mark.view.ca.result.data'])->get('/exams/{examId}/students/{studentId}/ca-results/prepare', [MarksController::class, 'prepareCaResultsByExam'])
    ->name('exams.students.ca-results.prepare');

// Prepare CA data for a specific student in a specific exam
Route::middleware(['permission:schoolAdmin.mark.view.ca.evaluation.data'])->get('/exams/{examId}/students/{studentId}/ca-data/prepare', [MarksController::class, 'prepareCaData'])
    ->name('exams.students.ca-data.prepare');

// Prepare exam data for a specific student in a specific exam
Route::middleware(['permission:schoolAdmin.mark.view.exam.evaluation.data'])->get('/exams/{examId}/students/{studentId}/exam-data/prepare', [MarksController::class, 'prepareExamData'])
    ->name('exams.students.exam-data.prepare');
