<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResitExamController;

// Get all resit exams
Route::middleware(['permission:schoolAdmin.resitExam.view'])->get('/', [ResitExamController::class, 'getAllResitExams'])
    ->name('resit-exams.index');

// Get details of a specific resit exam
Route::middleware(['permission:schoolAdmin.resitExam.show'])->get('/{resitExamId}', [ResitExamController::class, 'getResitExamDetails'])
    ->name('resit-exams.details');

// Update a specific resit exam
Route::middleware(['permission:schoolAdmin.resitExam.update'])->put('/{resitExamId}', [ResitExamController::class, 'updateResitExam'])
    ->name('resit-exams.update');

// Delete a specific resit exam
Route::middleware(['permission:schoolAdmin.resitExam.delete'])->delete('/{resitExamId}', [ResitExamController::class, 'deleteResitExam'])
    ->name('resit-exams.destroy');

// Add grading configuration to a resit exam
Route::middleware(['permission:schoolAdmin.resitExam.add.grading'])->post('/{resitExamId}/grading-configs/{gradesConfigId}', [ResitExamController::class, 'addResitExamGrading'])
    ->name('resit-exams.grading-configs.store');

// Bulk add grading configurations to resit exams
Route::middleware(['permission:schoolAdmin.resitExam.add.grading'])->post('/bulk-add-grading-configs', [ResitExamController::class, 'bulkAddExamGrading'])
    ->name('resit-exams.grading-configs.bulk-store');

// Bulk update resit exams
Route::middleware(['permission:schoolAdmin.resitExam.update'])->put('/bulk-update', [ResitExamController::class, 'bulkUpdateResitExam'])
    ->name('resit-exams.bulk-update');

// Bulk delete resit exams
Route::middleware(['permission:schoolAdmin.resitExam.delete'])->post('/bulk-delete', [ResitExamController::class, 'bulkDeleteResitExam'])
    ->name('resit-exams.bulk-delete');
