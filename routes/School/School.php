<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\School\SchoolController;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {

    Route::get('/', [SchoolController::class, 'getSchoolDetails'])
        ->name('schools.details');

    Route::put('/', [SchoolController::class, 'updateSchool'])
        ->name('schools.update');

    Route::delete('/', [SchoolController::class, 'deleteSchool'])
        ->name('schools.destroy');

    Route::post('/upload-school-logo', [SchoolController::class, "uploadSchoolLogo"]);
});
