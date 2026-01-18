<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradeScale\SchoolGradeScaleCategoryController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class, 'permission:schoolAdmin.grades.config.view'])->group(function () {
    Route::get('/scale-categories', [SchoolGradeScaleCategoryController::class, 'getSchoolGradeScaleCategories'])
        ->name('schoolGradeScaleCategories.index');

    Route::get('/scale-category/{schoolGradeScaleCategoryId}/scale', [SchoolGradeScaleCategoryController::class, 'getSchoolGradeScaleSchoolGradeCategoryId'])
        ->name('schoolGradeScaleCategory.scale');
});
