<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolBranchesController;
Route::middleware(['auth:sanctum'])->group(function () {
    // Delete a specific school branch
    Route::middleware(['permission:schoolAdmin.schoolBranch.delete'])->delete('/{branchId}', [SchoolBranchesController::class, 'deleteSchoolBranch'])
        ->name('school-branches.destroy');

    // Update a specific school branch
    Route::put('/{branchId}', [SchoolBranchesController::class, 'updateSchoolBranch'])
        ->name('school-branches.update');

    Route::middleware(['permission:appAdmin.schoolBranch.view'])->get('/', [SchoolBranchesController::class, 'getAllSchoolBranches'])
        ->name('school-branches.index');
});

