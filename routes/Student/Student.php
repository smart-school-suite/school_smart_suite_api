<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentController;

// Deactivate a specific student account
    Route::middleware(['permission:schoolAdmin.student.deactivate'])->post('/{studentId}/deactivate', [StudentController::class, 'deactivateAccount'])
        ->name('students.deactivate');

    // Activate a specific student account
    Route::middleware(['permission:schoolAdmin.student.activate'])->post('/{studentId}/activate', [StudentController::class, 'activateAccount'])
        ->name('students.activate');

    // Mark a specific student as a dropout
    Route::middleware(['permission:schoolAdmin.student.mark.student.as.dropout'])->post('/{studentId}/dropout', [StudentController::class, 'markStudentAsDropout'])
        ->name('students.dropout');

    // Get all student dropouts
    Route::middleware(['permission:schoolAdmin.student.view.student.dropout'])->get('/dropouts', [StudentController::class, 'getStudentDropoutList'])
        ->name('students.dropouts.index');

    // Reinstate a dropped-out student
    Route::middleware(['permission:schoolAdmin.student.reinstate.dropout.student'])->post('/dropouts/{studentDropoutId}/reinstate', [StudentController::class, 'reinstateDropedOutStudent'])
        ->name('students.dropouts.reinstate');

    // Bulk delete students
    Route::middleware(['permission:schoolAdmin.student.delete.student.dropout'])->post('/bulk-delete', [StudentController::class, 'bulkDeleteStudent'])
        ->name('students.bulk-delete');

    // Bulk activate students
    Route::middleware(['permission:schoolAdmin.student.activate'])->post('/bulk-activate', [StudentController::class, 'bulkActivateStudent'])
        ->name('students.bulk-activate');

    // Bulk deactivate students
    Route::middleware(['permission:schoolAdmin.student.deactivate'])->post('/bulk-deactivate', [StudentController::class, 'bulkDeActivateStudent'])
        ->name('students.bulk-deactivate');

    // Bulk mark students as dropouts
    Route::middleware(['permission:schoolAdmin.student.mark.student.as.dropout'])->post('/bulk-dropout', [StudentController::class, 'bulkMarkStudentAsDropout'])
        ->name('students.bulk-dropout');

    // Bulk update students
    Route::middleware(['permission:schoolAdmin.student.update'])->patch('/bulk-update', [StudentController::class, 'bulkUpdateStudent'])
        ->name('students.bulk-update');

    // Get all students
    Route::middleware(['permission:schoolAdmin.student.view.students'])->get('/students', [StudentController::class, 'getStudents'])
        ->name('students.index');

    // Get details of a specific student
    Route::middleware(['permission:schoolAdmin.student.view.student.details'])->get('/{studentId}', [StudentController::class, 'getStudentDetails'])
        ->name('students.show');

    // Update a specific student
    Route::middleware(['permission:schoolAdmin.student.update'])->put('/{studentId}', [StudentController::class, 'updateStudent'])
        ->name('students.update');

    // Delete a specific student
    Route::middleware(['permission:schoolAdmin.student.delete.student'])->delete('/{studentId}', [StudentController::class, 'deleteStudent'])
        ->name('students.destroy');

    Route::post('/avatar/upload', [StudentController::class, 'uploadProfilePicture'])
    ->name('student.avatar.upload');

    Route::delete('/avatar/delete', [StudentController::class, 'deleteProfilePicture'])
    ->name('student.avatar.delete');

    Route::get('/{studentId}/profile', [StudentController::class, 'getStudentProfileDetails'])->name('get.student.profile.details');

    Route::post('/bulk-reinstate/dropout', [StudentController::class, "bulkReinstateDropedOutStudent"])->name("Bulk.reinstate.dropdout.student");
