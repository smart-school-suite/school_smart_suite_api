<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\School\SchoolBranchController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {

    Route::get('/creation-options', [SchoolBranchController::class, 'getCreationOptions'])
        ->name('school-branches.creation-options');

    Route::post('/create', [SchoolBranchController::class, 'createSchoolBranch'])
        ->name('school-branches.store');

    Route::delete('/', [SchoolBranchController::class, 'deleteSchoolBranch'])
        ->name('school-branches.destroy');

    Route::put('/', [SchoolBranchController::class, 'updateSchoolBranch'])
        ->name('school-branches.update');

    Route::get('/', [SchoolBranchController::class, "getBranchDetails"])->name('school-branches.details');

});

