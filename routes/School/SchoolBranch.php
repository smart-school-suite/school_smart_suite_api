<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolBranchesController;
use App\Http\Middleware\IdentifyTenant;

// Publicly accessible route for school branch registration
Route::post('/school-branches', [SchoolBranchesController::class, 'createSchoolBranch'])
    ->name('school-branches.store');

Route::middleware(['auth:sanctum'])->group(function () {
    // Delete a specific school branch
    Route::middleware(['permission:schoolAdmin.schoolBranch.delete'])->delete('/school-branches/{branchId}', [SchoolBranchesController::class, 'deleteSchoolBranch'])
        ->name('school-branches.destroy');

    // Update a specific school branch
    Route::middleware(['permission:schoolAdmin.schoolBranch.update'])->put('/school-branches/{branchId}', [SchoolBranchesController::class, 'updateSchoolBranch'])
        ->name('school-branches.update');

    Route::middleware(['permission:appAdmin.schoolBranch.view'])->get('/school-branches', [SchoolBranchesController::class, 'getAllSchoolBranches'])
        ->name('school-branches.index');
});

