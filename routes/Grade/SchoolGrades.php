<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolGradeConfigController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    // Get the school grade configuration for the current tenant
    Route::get('/school-grade-config', [SchoolGradeConfigController::class, 'getSchoolGradesConfig'])
        ->name('school-grade-config.index');
});
