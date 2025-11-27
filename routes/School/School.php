<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\School\SchoolController;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    // Get details of the authenticated school
    Route::middleware(['permission:schoolAdmin.school.show'])->get('/', [SchoolController::class, 'getSchoolDetails'])
        ->name('schools.details');

    // Update the authenticated school
    Route::middleware(['permission:schoolAdmin.school.update'])->put('/', [SchoolController::class, 'updateSchool'])
        ->name('schools.update');

    // Delete a specific school
    Route::middleware(['permission:schoolAdmin.school.delete'])->delete('/', [SchoolController::class, 'deleteSchool'])
        ->name('schools.destroy');
    Route::post('/upload-school-logo', [SchoolController::class, "uploadSchoolLogo"]);
});
