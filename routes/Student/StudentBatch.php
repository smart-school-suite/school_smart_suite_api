<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentBatch\StudentBatchController;

    Route::post('/', [StudentBatchController::class, 'createStudentBatch'])
        ->name('student-batches.store');

    Route::get('/', [StudentBatchController::class, 'getStudentBatch'])
        ->name('student-batches.index');

    Route::put('/{batchId}', [StudentBatchController::class, 'updateStudentBatch'])
        ->name('student-batches.update');


    Route::delete('/{batchId}', [StudentBatchController::class, 'deleteStudentBatch'])
        ->name('student-batches.destroy');

    Route::post('/{batchId}/activate', [StudentBatchController::class, 'activateStudentBatch'])
        ->name('student-batches.activate');

    Route::post('/{batchId}/deactivate', [StudentBatchController::class, 'deactivateStudentBatch'])
        ->name('student-batches.deactivate');

    Route::post('/assign-graduation-dates', [StudentBatchController::class, 'assignGradDatesBySpecialty'])
        ->name('student-batches.assign-graduation-dates');

    Route::post('/bulk-delete', [StudentBatchController::class, 'bulkDeleteStudentBatch'])
        ->name('student-batches.bulk-delete');

    Route::post('/bulk-activate', [StudentBatchController::class, 'bulkActivateStudentBatch'])
        ->name('student-batches.bulk-activate');

    Route::post('/bulk-deactivate', [StudentBatchController::class, 'bulkDeactivateStudentBatch'])
        ->name('student-batches.bulk-deactivate');

    Route::patch('/bulk-update', [StudentBatchController::class, 'bulkUpdateStudentBatch'])
        ->name('student-batches.bulk-update');

    Route::get('/details/{batchId}', [StudentBatchcontroller::class, 'getStudentBatchDetails'])->name("get.student.batch.details");
