<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\Semester\SchoolSemesterController;


Route::post('/', [SchoolSemesterController::class, 'createSchoolSemester'])
->name('school-semesters.store');

Route::put('/{schoolSemesterId}', [SchoolSemesterController::class, 'updateSchoolSemester'])
->name('school-semesters.update');

Route::get('/', [SchoolSemesterController::class, 'getSchoolSemester'])
->name('school-semesters.index');

Route::delete('/{schoolSemesterId}', [SchoolSemesterController::class, 'deleteSchoolSemester'])
->name('school-semesters.destroy');

Route::get('/{schoolSemesterId}', [SchoolSemesterController::class, 'getSchoolSemesterDetails'])
->name('school-semesters.show');

Route::post('/bulk-delete', [SchoolSemesterController::class, 'bulkDeleteSchoolSemester'])
->name('school-semesters.bulk-delete');

Route::patch('/bulk-update', [SchoolSemesterController::class, 'bulkUpdateSchoolSemester'])
->name('school-semesters.bulk-update');

Route::get('/active', [SchoolSemesterController::class, 'getActiveSchoolSemesters'])
->name('school-semesters.active');
