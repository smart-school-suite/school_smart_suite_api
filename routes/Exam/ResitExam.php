<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResitExamController;

// Get all resit exams
Route::get('/resit-exams', [ResitExamController::class, 'getAllResitExams'])
    ->name('resit-exams.index');

// Get details of a specific resit exam
Route::get('/resit-exams/details', [ResitExamController::class, 'getResitExamDetails'])
    ->name('resit-exams.details'); // Consider changing URI to '/resit-exams/{resitExamId}' for consistency

// Update a specific resit exam
Route::put('/resit-exams/{resitExamId}', [ResitExamController::class, 'updateResitExam'])
    ->name('resit-exams.update');

// Delete a specific resit exam
Route::delete('/resit-exams/{resitExamId}', [ResitExamController::class, 'deleteResitExam'])
    ->name('resit-exams.destroy');

// Add grading configuration to a resit exam
Route::post('/resit-exams/{resitExamId}/grading-configs/{gradesConfigId}', [ResitExamController::class, 'addResitExamGrading'])
    ->name('resit-exams.grading-configs.store');

// Bulk add grading configurations to resit exams
Route::post('/resit-exams/bulk-add-grading-configs', [ResitExamController::class, 'bulkAddExamGrading'])
    ->name('resit-exams.grading-configs.bulk-store');

// Bulk update resit exams
Route::put('/resit-exams/bulk-update', [ResitExamController::class, 'bulkUpdateResitExam'])
    ->name('resit-exams.bulk-update');

// Bulk delete resit exams
Route::delete('/resit-exams/bulk-delete/{resitExamIds}', [ResitExamController::class, 'bulkDeleteResitExam'])
    ->name('resit-exams.bulk-delete');
