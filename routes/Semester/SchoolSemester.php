<?php

use illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\SchoolSemesterController;

Route::middleware(['permission:schoolAdmin.schoolSemester.create'])->post('/school-semesters', [SchoolSemesterController::class, 'createSchoolSemester'])
->name('school-semesters.store');

// Update a specific school semester
Route::middleware(['permission:schoolAdmin.schoolSemester.update'])->put('/school-semesters/{schoolSemesterId}', [SchoolSemesterController::class, 'updateSchoolSemester'])
->name('school-semesters.update');

// Get all school semesters
Route::middleware(['permission:schoolAdmin.schoolSemester.view'])->get('/school-semesters', [SchoolSemesterController::class, 'getSchoolSemester'])
->name('school-semesters.index');

// Delete a specific school semester
Route::middleware(['permission:schoolAdmin.schoolSemester.delete'])->delete('/school-semesters/{schoolSemesterId}', [SchoolSemesterController::class, 'deleteSchoolSemester'])
->name('school-semesters.destroy');

// Get details of a specific school semester
Route::middleware(['permission:schoolAdmin.schoolSemester.show'])->get('/school-semesters/{schoolSemesterId}', [SchoolSemesterController::class, 'getSchoolSemesterDetails'])
->name('school-semesters.show');

// Bulk delete school semesters
Route::middleware(['permission:permission:schoolAdmin.schoolSemester.delete'])->delete('/school-semesters/bulk-delete/{schoolSemesterIds}', [SchoolSemesterController::class, 'bulkDeleteSchoolSemester'])
->name('school-semesters.bulk-delete');

// Bulk update school semesters
Route::middleware(['permission:schoolAdmin.schoolSemester.update'])->put('/school-semesters/bulk-update', [SchoolSemesterController::class, 'bulkUpdateSchoolSemester'])
->name('school-semesters.bulk-update');

// Get active school semesters
Route::middleware(['permission:schoolAdmin.schoolSemester.view.active'])->get('/school-semesters/active', [SchoolSemesterController::class, 'getActiveSchoolSemesters'])
->name('school-semesters.active');
