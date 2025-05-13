<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
// Create a new department
    Route::post('/departments', [DepartmentController::class, 'createDepartment'])
        ->name('departments.store');

    // Get all departments (potentially admin-only)
    Route::get('/departments', [DepartmentController::class, 'getDepartments'])
        ->name('departments.index');

    // Get details of a specific department
    Route::get('/departments/{departmentId}', [DepartmentController::class, 'getDepartmentDetails'])
        ->name('departments.show');

    // Get departments for the authenticated user (consider renaming if it fetches all)
    Route::get('/my/departments', [DepartmentController::class, 'getDepartments'])
        ->name('my-departments.index');

    // Update a specific department
    Route::put('/departments/{departmentId}', [DepartmentController::class, 'updateDepartment'])
        ->name('departments.update');

    // Delete a specific department
    Route::delete('/departments/{departmentId}', [DepartmentController::class, 'deleteDepartment'])
        ->name('departments.destroy');

    // Activate a specific department
    Route::post('/departments/{departmentId}/activate', [DepartmentController::class, 'activateDepartment'])
        ->name('departments.activate');

    // Deactivate a specific department
    Route::post('/departments/{departmentId}/deactivate', [DepartmentController::class, 'deactivateDepartment'])
        ->name('departments.deactivate');

    // Bulk update departments
    Route::put('/departments/bulk-update', [DepartmentController::class, 'bulkUpdateDepartment'])
        ->name('departments.bulk-update');

    // Bulk delete departments
    Route::delete('/departments/bulk-delete/{departmentIds}', [DepartmentController::class, 'bulkDeleteDepartment'])
        ->name('departments.bulk-delete');

    // Bulk activate departments
    Route::post('/departments/bulk-activate/{departmentIds}', [DepartmentController::class, 'bulkActivateDepartment'])
        ->name('departments.bulk-activate');

    // Bulk deactivate departments
    Route::post('/departments/bulk-deactivate/{departmentIds}', [DepartmentController::class, 'bulkDeactivateDepartment'])
        ->name('departments.bulk-deactivate');
