<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolsController;

Route::post('/schools', [SchoolsController::class, 'createSchool'])
    ->name('schools.store');

Route::middleware(['auth:sanctum'])->group(function () {
    // Get details of the authenticated school
    Route::get('/schools/details/{schoolId}', [SchoolsController::class, 'getSchoolDetails'])
        ->name('schools.details');

    // Update the authenticated school
    Route::put('/schools', [SchoolsController::class, 'updateSchool'])
        ->name('schools.update');

    // Delete a specific school
    Route::delete('/schools/{schoolId}', [SchoolsController::class, 'deleteSchool'])
        ->name('schools.destroy');
});
