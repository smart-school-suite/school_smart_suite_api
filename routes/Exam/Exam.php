<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamsController;

// Create a new exam
Route::post('/exams', [ExamsController::class, 'createExam'])
    ->name('exams.store');

// Get all exams
Route::get('/exams', [ExamsController::class, 'getExams'])
    ->name('exams.index');

// Get details of a specific exam
Route::get('/exams/{examId}', [ExamsController::class, 'getExamDetails'])
    ->name('exams.show');

// Update a specific exam
Route::put('/exams/{examId}', [ExamsController::class, 'updateExam'])
    ->name('exams.update');

// Delete a specific exam
Route::delete('/exams/{examId}', [ExamsController::class, 'deleteExam'])
    ->name('exams.destroy');

// Get letter grades associated with an exam
Route::get('/exams/{examId}/letter-grades', [ExamsController::class, 'associateWeightedMarkWithLetterGrades'])
    ->name('exams.letter-grades');

// Get exams accessed by a specific student
Route::get('/students/{studentId}/accessed-exams', [ExamsController::class, 'getAccessedExams'])
    ->name('students.accessed-exams.index');

// Add grading configuration to an exam
Route::post('/exams/{examId}/grading-configs/{gradesConfigId}', [ExamsController::class, 'addExamGrading'])
    ->name('exams.grading-configs.store');

// Get all resit exams
Route::get('/resit-exams', [ExamsController::class, 'getResitExams'])
    ->name('resit-exams.index');

// Bulk delete exams
Route::delete('/exams/bulk-delete/{examIds}', [ExamsController::class, 'bulkDeleteExam'])
    ->name('exams.bulk-delete');

// Bulk update exams
Route::put('/exams/bulk-update', [ExamsController::class, 'bulkUpdateExam'])
    ->name('exams.bulk-update');

// Bulk add grading configurations to exams
Route::post('/exams/bulk-add-grading-configs', [ExamsController::class, 'bulkAddExamGrading'])
    ->name('exams.grading-configs.bulk-store');
