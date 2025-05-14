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

    // Delete a specific student dropout record
    Route::middleware(['permission:schoolAdmin.student.delete.student.dropout'])->delete('/students/dropouts/{studentDropoutId}', [StudentController::class, 'deleteStudentDropout'])
        ->name('students.dropouts.destroy');

    // Reinstate a dropped-out student
    Route::middleware(['permission:schoolAdmin.student.reinstate.dropout.student'])->post('/students/dropouts/{studentDropoutId}/reinstate', [StudentController::class, 'reinstateDropedOutStudent'])
        ->name('students.dropouts.reinstate');

    // Bulk delete students
    Route::middleware(['permission:schoolAdmin.student.delete.student.dropout'])->delete('/students/bulk-delete/{studentIds}', [StudentController::class, 'bulkDeleteStudent'])
        ->name('students.bulk-delete');

    // Bulk activate students
    Route::middleware(['permission:schoolAdmin.student.activate'])->post('/students/bulk-activate/{studentIds}', [StudentController::class, 'bulkActivateStudent'])
        ->name('students.bulk-activate');

    // Bulk deactivate students
    Route::middleware(['permission:schoolAdmin.student.deactivate'])->post('/students/bulk-deactivate/{studentIds}', [StudentController::class, 'bulkDeActivateStudent'])
        ->name('students.bulk-deactivate');

    // Bulk mark students as dropouts
    Route::middleware(['permission:schoolAdmin.student.mark.student.as.dropout'])->post('/students/bulk-dropout', [StudentController::class, 'bulkMarkStudentAsDropout'])
        ->name('students.bulk-dropout');

    // Bulk update students
    Route::middleware(['permission:schoolAdmin.student.update'])->put('/students/bulk-update', [StudentController::class, 'bulkUpdateStudent'])
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
