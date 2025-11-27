<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradeScale\SchoolGradeScaleController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class, 'permission:schoolAdmin.grades.config.view'])->group(function () {
    // Get the school grade configuration for the current tenant
    Route::get('/school-grade-config', [SchoolGradeScaleController::class, 'getSchoolGradesConfig'])
        ->name('school-grade-config.index');

    Route::get('/school-grade-config/{schoolGradeConfigId}/grading', [SchoolGradeScaleController::class, 'getGradingBySchoolGradeCongfig'])
        ->name('school-grade-config.grading.index');
});
