<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarksController;

// Add CA scores for a student in a course
Route::post('/ca-scores', [MarksController::class, 'createCaMark'])
    ->name('ca-scores.store');

// Add exam scores for a student in a course
Route::post('/exam-scores', [MarksController::class, 'createExamMark'])
    ->name('exam-scores.store');

// Update CA score for a student in a course
Route::put('/ca-scores', [MarksController::class, 'updateCaMark'])
    ->name('ca-scores.update');

// Update exam score for a student in a course
Route::put('/exam-scores', [MarksController::class, 'updateExamMark'])
    ->name('exam-scores.update');

// Delete a specific student mark (CA or Exam)
Route::delete('/student-marks/{markId}', [MarksController::class, 'deleteMark'])
    ->name('student-marks.destroy');

// Get scores for a specific student in a specific exam
Route::get('/students/{studentId}/exams/{examId}/scores', [MarksController::class, 'getMarksByExamStudent'])
    ->name('students.exams.scores.show');

// Get details of a specific mark (CA or Exam)
Route::get('/student-marks/details', [MarksController::class, 'getMarkDetails'])
    ->name('student-marks.details'); // Consider changing URI to '/student-marks/{markId}' for consistency

// Get courses accessed within a specific exam (with letter grades)
Route::get('/exams/{examId}/accessed-courses', [MarksController::class, 'getAccessedCoursesWithLettergrades'])
    ->name('exams.accessed-courses.index');

// Prepare CA results data for a specific student in a specific exam
Route::get('/exams/{examId}/students/{studentId}/ca-results/prepare', [MarksController::class, 'prepareCaResultsByExam'])
    ->name('exams.students.ca-results.prepare');

// Prepare CA data for a specific student in a specific exam
Route::get('/exams/{examId}/students/{studentId}/ca-data/prepare', [MarksController::class, 'prepareCaData'])
    ->name('exams.students.ca-data.prepare');

// Prepare exam data for a specific student in a specific exam
Route::get('/exams/{examId}/students/{studentId}/exam-data/prepare', [MarksController::class, 'prepareExamData'])
    ->name('exams.students.exam-data.prepare');
