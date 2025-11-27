<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\Semester\SchoolSemesterController;


Route::middleware(['permission:schoolAdmin.schoolSemester.create'])->post('/', [SchoolSemesterController::class, 'createSchoolSemester'])
->name('school-semesters.store');

// Update a specific school semester
Route::middleware(['permission:schoolAdmin.schoolSemester.update'])->put('/{schoolSemesterId}', [SchoolSemesterController::class, 'updateSchoolSemester'])
->name('school-semesters.update');

// Get all school semesters
Route::middleware(['permission:schoolAdmin.schoolSemester.view'])->get('/', [SchoolSemesterController::class, 'getSchoolSemester'])
->name('school-semesters.index');

// Delete a specific school semester
Route::middleware(['permission:schoolAdmin.schoolSemester.delete'])->delete('/{schoolSemesterId}', [SchoolSemesterController::class, 'deleteSchoolSemester'])
->name('school-semesters.destroy');

// Get details of a specific school semester
Route::middleware(['permission:schoolAdmin.schoolSemester.show'])->get('/{schoolSemesterId}', [SchoolSemesterController::class, 'getSchoolSemesterDetails'])
->name('school-semesters.show');

// Bulk delete school semesters
Route::middleware(['permission:schoolAdmin.schoolSemester.delete'])->post('/bulk-delete', [SchoolSemesterController::class, 'bulkDeleteSchoolSemester'])
->name('school-semesters.bulk-delete');

// Bulk update school semesters
Route::middleware(['permission:schoolAdmin.schoolSemester.update'])->patch('/bulk-update', [SchoolSemesterController::class, 'bulkUpdateSchoolSemester'])
->name('school-semesters.bulk-update');

// Get active school semesters
Route::middleware(['permission:schoolAdmin.schoolSemester.view.active'])->get('/active', [SchoolSemesterController::class, 'getActiveSchoolSemesters'])
->name('school-semesters.active');
