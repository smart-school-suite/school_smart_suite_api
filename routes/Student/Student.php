<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\StudentController;

// Deactivate a specific student account
    Route::post('/students/{studentId}/deactivate', [StudentController::class, 'deactivateAccount'])
        ->name('students.deactivate');

    // Activate a specific student account
    Route::post('/students/{studentId}/activate', [StudentController::class, 'activateAccount'])
        ->name('students.activate');

    // Mark a specific student as a dropout
    Route::post('/students/{studentId}/dropout', [StudentController::class, 'markStudentAsDropout'])
        ->name('students.dropout');

    // Get all student dropouts
    Route::get('/students/dropouts', [StudentController::class, 'getStudentDropoutList'])
        ->name('students.dropouts.index');

    // Delete a specific student dropout record
    Route::delete('/students/dropouts/{studentDropoutId}', [StudentController::class, 'deleteStudentDropout'])
        ->name('students.dropouts.destroy');

    // Reinstate a dropped-out student
    Route::post('/students/dropouts/{studentDropoutId}/reinstate', [StudentController::class, 'reinstateDropedOutStudent'])
        ->name('students.dropouts.reinstate');

    // Bulk delete students
    Route::delete('/students/bulk-delete/{studentIds}', [StudentController::class, 'bulkDeleteStudent'])
        ->name('students.bulk-delete');

    // Bulk activate students
    Route::post('/students/bulk-activate/{studentIds}', [StudentController::class, 'bulkActivateStudent'])
        ->name('students.bulk-activate');

    // Bulk deactivate students
    Route::post('/students/bulk-deactivate/{studentIds}', [StudentController::class, 'bulkDeActivateStudent'])
        ->name('students.bulk-deactivate');

    // Bulk mark students as dropouts
    Route::post('/students/bulk-dropout', [StudentController::class, 'bulkMarkStudentAsDropout'])
        ->name('students.bulk-dropout');

    // Bulk update students
    Route::put('/students/bulk-update', [StudentController::class, 'bulkUpdateStudent'])
        ->name('students.bulk-update');

    // Get all students
    Route::get('/students', [StudentController::class, 'getStudents'])
        ->name('students.index');

    // Get details of a specific student
    Route::get('/students/{studentId}', [StudentController::class, 'getStudentDetails'])
        ->name('students.show');

    // Update a specific student
    Route::put('/students/{studentId}', [StudentController::class, 'updateStudent'])
        ->name('students.update');

    // Delete a specific student
    Route::delete('/students/{studentId}', [StudentController::class, 'deleteStudent'])
        ->name('students.destroy');
