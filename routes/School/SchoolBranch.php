<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolBranchesController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    // Delete a specific school branch
    Route::middleware(['permission:schoolAdmin.schoolBranch.delete'])->delete('/', [SchoolBranchesController::class, 'deleteSchoolBranch'])
        ->name('school-branches.destroy');

    // Update a specific school branch
    Route::put('/', [SchoolBranchesController::class, 'updateSchoolBranch'])
        ->name('school-branches.update');

    Route::get('/', [SchoolBranchesController::class, "getBranchDetails"])->name('school-branches.details');

});

