<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
Route::middleware(['auth:sanctum'])->post('/create-department', [DepartmentController::class, 'createDepartment']);
Route::middleware(['auth:sanctum'])->get('/my-departments', [DepartmentController::class, 'getDepartments']);
Route::middleware(['auth:sanctum'])->get('/department-details/{department_id}', [DepartmentController::class, 'getDepartmentDetails']);
Route::middleware(['auth:sanctum'])->put('/update-department/{department_id}', [DepartmentController::class, 'updateDepartment']);
Route::middleware(['auth:sanctum'])->delete('/delete-department/{department_id}', [DepartmentController::class, 'deleteDepartment']);
Route::middleware(['auth:sanctum'])->post("/deactivateDepartment/{departmentId}", [DepartmentController::class, "deactivateDepartment"]);
Route::middleware(['auth:sanctum'])->post("/activateDepartment/{departmentId}", [DepartmentController::class, "activateDepartment"]);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateDepartment', [DepartmentController::class, 'bulkUpdateDepartment']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteDepartment/{departmentIds}', [DepartmentController::class, 'bulkDeleteDepartment']);
Route::middleware(['auth:sanctum'])->post('/bulkDeactivateDepartment/{departmentIds}', [DepartmentController::class, 'bulkDeactivateDepartment']);
Route::middleware(['auth:sanctum'])->post('/bulkActivateDepartment/{departmentIds}', [DepartmentController::class, 'bulkActivateDepartment']);
