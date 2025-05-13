<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentBatchcontroller;

// Create a new student batch
    Route::post('/student-batches', [StudentBatchController::class, 'createStudentBatch'])
        ->name('student-batches.store');

    // Get all student batches
    Route::get('/student-batches', [StudentBatchController::class, 'getStudentBatch'])
        ->name('student-batches.index');

    // Get graduation dates for a specific batch
    Route::get('/student-batches/{batchId}/graduation-dates', [StudentBatchController::class, 'getGraduationDatesByBatch'])
        ->name('student-batches.graduation-dates.index');

    // Update a specific student batch
    Route::put('/student-batches/{batchId}', [StudentBatchController::class, 'updateStudentBatch'])
        ->name('student-batches.update');

    // Delete a specific student batch
    Route::delete('/student-batches/{batchId}', [StudentBatchController::class, 'deleteStudentBatch'])
        ->name('student-batches.destroy');

    // Activate a specific student batch
    Route::post('/student-batches/{batchId}/activate', [StudentBatchController::class, 'activateStudentBatch'])
        ->name('student-batches.activate');

    // Deactivate a specific student batch
    Route::post('/student-batches/{batchId}/deactivate', [StudentBatchController::class, 'deactivateStudentBatch'])
        ->name('student-batches.deactivate');

    // Assign graduation dates to a batch (by specialty)
    Route::post('/student-batches/assign-graduation-dates', [StudentBatchController::class, 'assignGradDatesBySpecialty'])
        ->name('student-batches.assign-graduation-dates');

    // Bulk delete student batches
    Route::delete('/student-batches/bulk-delete/{batchIds}', [StudentBatchController::class, 'bulkDeleteStudentBatch'])
        ->name('student-batches.bulk-delete');

    // Bulk activate student batches
    Route::post('/student-batches/bulk-activate/{batchIds}', [StudentBatchController::class, 'bulkActivateStudentBatch'])
        ->name('student-batches.bulk-activate');

    // Bulk deactivate student batches
    Route::post('/student-batches/bulk-deactivate/{batchIds}', [StudentBatchController::class, 'bulkDeactivateStudentBatch'])
        ->name('student-batches.bulk-deactivate');

    // Bulk update student batches
    Route::put('/student-batches/bulk-update', [StudentBatchController::class, 'bulkUpdateStudentBatch'])
        ->name('student-batches.bulk-update');

    // Bulk assign graduation dates to batches (by specialty)
    Route::post('/student-batches/bulk-assign-graduation-dates', [StudentBatchController::class, 'bulkAssignGradDateBySpecialty'])
        ->name('student-batches.bulk-assign-graduation-dates');
