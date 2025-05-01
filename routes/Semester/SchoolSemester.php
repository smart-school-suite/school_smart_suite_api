<?php

use illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\SchoolSemesterController;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/create-school-semester', [SchoolSemesterController::class, 'createSchoolSemester']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->put("/update-school-semester/{schoolSemesterId}", [SchoolSemesterController::class, 'updateSchoolSemester']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/school-semeters", [SchoolSemesterController::class, 'getSchoolSemester']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/delete-school-semeter/{schoolSemesterId}", [SchoolSemesterController::class, 'deleteSchoolSemester']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/schoolSemesterDetails/{schoolSemesterId}", [SchoolSemesterController::class, 'getSchoolSemesterDetails']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->delete("/bulkDeleteSchoolSemesters/{schoolSemesterIds}", [SchoolSemesterController::class, 'bulkDeleteSchoolSemester']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->put("/bulkUpdateSchoolSemesters", [SchoolSemesterController::class, 'bulkUpdateSchoolSemester']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/getActiveSemesters", [SchoolSemesterController::class, 'getActiveSchoolSemesters']);
