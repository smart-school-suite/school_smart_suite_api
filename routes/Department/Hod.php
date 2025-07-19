<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HodController;

// Assign a Head of Department
Route::middleware(['permission:schoolAdmin.hod.create'])->post('/', [HodController::class, 'assignHeadOfDepartment'])
    ->name('head-of-departments.store');

// Get all assigned Heads of Department
Route::middleware(['permission:schoolAdmin.hod.view'])->get('/', [HodController::class, 'getAssignedHods'])
    ->name('head-of-departments.index');

// Get details of a specific Head of Department assignment
Route::middleware(['permission:schoolAdmin.hod.show'])->get('/{hodId}', [HodController::class, 'getHodDetails'])
    ->name('head-of-departments.show');

// Remove a specific Head of Department assignment
Route::middleware(['permission:schoolAdmin.hod.delete'])->delete('/{hodId}', [HodController::class, 'removeHod'])
    ->name('head-of-departments.destroy');

// Bulk remove Head of Department assignments (consider using DELETE with a request body)
Route::middleware(['permission:schoolAdmin.hod.delete'])->post('/bulk-remove', [HodController::class, 'bulkRemoveHod'])
    ->name('head-of-departments.bulk-remove');
