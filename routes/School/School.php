<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolsController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Get details of the authenticated school
    Route::middleware(['permission:schoolAdmin.school.show'])->get('/{schoolId}', [SchoolsController::class, 'getSchoolDetails'])
        ->name('schools.details');

    // Update the authenticated school
    Route::middleware(['permission:schoolAdmin.school.update'])->put('/{schoolId}', [SchoolsController::class, 'updateSchool'])
        ->name('schools.update');

    // Delete a specific school
    Route::middleware(['permission:schoolAdmin.school.delete'])->delete('/{schoolId}', [SchoolsController::class, 'deleteSchool'])
        ->name('schools.destroy');
});
