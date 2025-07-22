<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolsController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    // Get details of the authenticated school
    Route::middleware(['permission:schoolAdmin.school.show'])->get('/', [SchoolsController::class, 'getSchoolDetails'])
        ->name('schools.details');

    // Update the authenticated school
    Route::middleware(['permission:schoolAdmin.school.update'])->put('/', [SchoolsController::class, 'updateSchool'])
        ->name('schools.update');

    // Delete a specific school
    Route::middleware(['permission:schoolAdmin.school.delete'])->delete('/', [SchoolsController::class, 'deleteSchool'])
        ->name('schools.destroy');
    Route::post('/upload-school-logo', [SchoolsController::class, "uploadSchoolLogo"]);
});
