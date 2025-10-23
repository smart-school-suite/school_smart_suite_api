<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentBatchcontroller;

// Create a new student batch
    Route::middleware(['permission:schoolAdmin.student.batch.create'])->post('/', [StudentBatchController::class, 'createStudentBatch'])
        ->name('student-batches.store');

    // Get all student batches
    Route::middleware(['permission:schoolAdmin.student.batch.view'])->get('/', [StudentBatchController::class, 'getStudentBatch'])
        ->name('student-batches.index');

    // Get graduation dates for a specific batch
    Route::middleware(['permission:schoolAdmin.student.batch.view.graduation.dates'])->get('/{batchId}/graduation-dates', [StudentBatchController::class, 'getGraduationDatesByBatch'])
        ->name('student-batches.graduation-dates.index');

    // Update a specific student batch
    Route::middleware(['permission:schoolAdmin.student.batch.update'])->put('/{batchId}', [StudentBatchController::class, 'updateStudentBatch'])
        ->name('student-batches.update');

    // Delete a specific student batch
    Route::middleware(['permission:schoolAdmin.student.batch.delete'])->delete('/{batchId}', [StudentBatchController::class, 'deleteStudentBatch'])
        ->name('student-batches.destroy');

    // Activate a specific student batch
    Route::middleware(['permission:schoolAdmin.student.batch.activate'])->post('/{batchId}/activate', [StudentBatchController::class, 'activateStudentBatch'])
        ->name('student-batches.activate');

    // Deactivate a specific student batch
    Route::middleware(['permission:schoolAdmin.student.batch.deactivate'])->post('/{batchId}/deactivate', [StudentBatchController::class, 'deactivateStudentBatch'])
        ->name('student-batches.deactivate');

    // Assign graduation dates to a batch (by specialty)
    Route::middleware(['permission:schoolAdmin.student.batch.create.graduation.dates'])->post('/assign-graduation-dates', [StudentBatchController::class, 'assignGradDatesBySpecialty'])
        ->name('student-batches.assign-graduation-dates');

    // Bulk delete student batches
    Route::middleware(['permission:schoolAdmin.student.batch.delete'])->post('/bulk-delete', [StudentBatchController::class, 'bulkDeleteStudentBatch'])
        ->name('student-batches.bulk-delete');

    // Bulk activate student batches
    Route::middleware(['permission:schoolAdmin.student.batch.activate'])->post('/bulk-activate', [StudentBatchController::class, 'bulkActivateStudentBatch'])
        ->name('student-batches.bulk-activate');

    // Bulk deactivate student batches
    Route::middleware(['permission:schoolAdmin.student.batch.deactivate'])->post('/bulk-deactivate', [StudentBatchController::class, 'bulkDeactivateStudentBatch'])
        ->name('student-batches.bulk-deactivate');

    // Bulk update student batches
    Route::middleware(['permission:schoolAdmin.student.batch.update'])->patch('/bulk-update', [StudentBatchController::class, 'bulkUpdateStudentBatch'])
        ->name('student-batches.bulk-update');

    // Bulk assign graduation dates to batches (by specialty)
    Route::middleware(['permission:schoolAdmin.student.batch.create.graduation.dates'])->post('/bulk-assign-graduation-dates', [StudentBatchController::class, 'bulkAssignGradDateBySpecialty'])
        ->name('student-batches.bulk-assign-graduation-dates');

    Route::get('/details/{batchId}', [StudentBatchcontroller::class, 'getStudentBatchDetails'])->name("get.student.batch.details");
