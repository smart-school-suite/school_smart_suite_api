<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
// Create a new department
    Route::middleware(['permission:schoolAdmin.department.create'])->post('/departments', [DepartmentController::class, 'createDepartment'])
        ->name('departments.store');

    // Get all departments (potentially admin-only)
    Route::middleware(['permission:schoolAdmin.department.view'])->get('/departments', [DepartmentController::class, 'getDepartments'])
        ->name('departments.index');

    // Get details of a specific department
    Route::middleware(['permission:schoolAdmin.department.show'])->get('/departments/{departmentId}', [DepartmentController::class, 'getDepartmentDetails'])
        ->name('departments.show');

    // Get departments for the authenticated user (consider renaming if it fetches all)
    Route::middleware(['permission:schoolAdmin.department.view'])->get('/my/departments', [DepartmentController::class, 'getDepartments'])
        ->name('my-departments.index');

    // Update a specific department
    Route::middleware(['permission:schoolAdmin.department.update'])->put('/departments/{departmentId}', [DepartmentController::class, 'updateDepartment'])
        ->name('departments.update');

    // Delete a specific department
    Route::middleware(['permission:schoolAdmin.department.delete'])->delete('/departments/{departmentId}', [DepartmentController::class, 'deleteDepartment'])
        ->name('departments.destroy');

    // Activate a specific department
    Route::middleware(['permission:schoolAdmin.department.activate'])->post('/departments/{departmentId}/activate', [DepartmentController::class, 'activateDepartment'])
        ->name('departments.activate');

    // Deactivate a specific department
    Route::middleware(['permission:schoolAdmin.department.deactivate'])->post('/departments/{departmentId}/deactivate', [DepartmentController::class, 'deactivateDepartment'])
        ->name('departments.deactivate');

    // Bulk update departments
    Route::middleware(['permission:schoolAdmin.department.update'])->put('/departments/bulk-update', [DepartmentController::class, 'bulkUpdateDepartment'])
        ->name('departments.bulk-update');

    // Bulk delete departments
    Route::middleware(['permission:schoolAdmin.department.delete'])->post('/departments/bulk-delete', [DepartmentController::class, 'bulkDeleteDepartment'])
        ->name('departments.bulk-delete');

    // Bulk activate departments
    Route::middleware(['permission:schoolAdmin.department.activate'])->post('/departments/bulk-activate', [DepartmentController::class, 'bulkActivateDepartment'])
        ->name('departments.bulk-activate');

    // Bulk deactivate departments
    Route::middleware(['permission:schoolAdmin.department.deactivate'])->post('/departments/bulk-deactivate', [DepartmentController::class, 'bulkDeactivateDepartment'])
        ->name('departments.bulk-deactivate');
