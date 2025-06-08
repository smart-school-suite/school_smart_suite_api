<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

// Deactivate a specific student account
    Route::middleware(['permission:schoolAdmin.student.deactivate'])->post('/students/{studentId}/deactivate', [StudentController::class, 'deactivateAccount'])
        ->name('students.deactivate');

    // Activate a specific student account
    Route::middleware(['permission:schoolAdmin.student.activate'])->post('/students/{studentId}/activate', [StudentController::class, 'activateAccount'])
        ->name('students.activate');

    // Mark a specific student as a dropout
    Route::middleware(['permission:schoolAdmin.student.mark.student.as.dropout'])->post('/students/{studentId}/dropout', [StudentController::class, 'markStudentAsDropout'])
        ->name('students.dropout');

    // Get all student dropouts
    Route::middleware(['permission:schoolAdmin.student.view.student.dropout'])->get('/students/dropouts', [StudentController::class, 'getStudentDropoutList'])
        ->name('students.dropouts.index');

    // Reinstate a dropped-out student
    Route::middleware(['permission:schoolAdmin.student.reinstate.dropout.student'])->post('/students/dropouts/{studentDropoutId}/reinstate', [StudentController::class, 'reinstateDropedOutStudent'])
        ->name('students.dropouts.reinstate');

    // Bulk delete students
    Route::middleware(['permission:schoolAdmin.student.delete.student.dropout'])->post('/students/bulk-delete', [StudentController::class, 'bulkDeleteStudent'])
        ->name('students.bulk-delete');

    // Bulk activate students
    Route::middleware(['permission:schoolAdmin.student.activate'])->post('/students/bulk-activate', [StudentController::class, 'bulkActivateStudent'])
        ->name('students.bulk-activate');

    // Bulk deactivate students
    Route::middleware(['permission:schoolAdmin.student.deactivate'])->post('/students/bulk-deactivate', [StudentController::class, 'bulkDeActivateStudent'])
        ->name('students.bulk-deactivate');

    // Bulk mark students as dropouts
    Route::middleware(['permission:schoolAdmin.student.mark.student.as.dropout'])->post('/students/bulk-dropout', [StudentController::class, 'bulkMarkStudentAsDropout'])
        ->name('students.bulk-dropout');

    // Bulk update students
    Route::middleware(['permission:schoolAdmin.student.update'])->patch('/students/bulk-update', [StudentController::class, 'bulkUpdateStudent'])
        ->name('students.bulk-update');

    // Get all students
    Route::middleware(['permission:schoolAdmin.student.view.students'])->get('/students', [StudentController::class, 'getStudents'])
        ->name('students.index');

    // Get details of a specific student
    Route::middleware(['permission:schoolAdmin.student.view.student.details'])->get('/students/{studentId}', [StudentController::class, 'getStudentDetails'])
        ->name('students.show');

    // Update a specific student
    Route::middleware(['permission:schoolAdmin.student.update'])->put('/students/{studentId}', [StudentController::class, 'updateStudent'])
        ->name('students.update');

    // Delete a specific student
    Route::middleware(['permission:schoolAdmin.student.delete.student'])->delete('/students/{studentId}', [StudentController::class, 'deleteStudent'])
        ->name('students.destroy');

    Route::post('/student/avatar/upload', [StudentController::class, 'uploadProfilePicture'])
    ->name('student.avatar.upload');

    Route::delete('/student/avatar/delete', [StudentController::class, 'deleteProfilePicture'])
    ->name('student.avatar.delete');
