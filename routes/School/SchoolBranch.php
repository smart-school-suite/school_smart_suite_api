<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\School\SchoolBranchController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    // Delete a specific school branch
    Route::middleware(['permission:schoolAdmin.schoolBranch.delete'])->delete('/', [SchoolBranchController::class, 'deleteSchoolBranch'])
        ->name('school-branches.destroy');

    // Update a specific school branch
    Route::put('/', [SchoolBranchController::class, 'updateSchoolBranch'])
        ->name('school-branches.update');

    Route::get('/', [SchoolBranchController::class, "getBranchDetails"])->name('school-branches.details');

});

