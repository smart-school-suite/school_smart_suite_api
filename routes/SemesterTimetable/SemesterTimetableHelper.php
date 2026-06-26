<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterTimetable\SemesterTimetableHelperController;

Route::get('/teachers/school-semester/{schoolSemesterId}', [SemesterTimetableHelperController::class, 'getTeachersSchooolSemesterId'])->name('get.teachers');
Route::post('available/fixed-teachers', [SemesterTimetableHelperController::class, 'getAvialableFixedTeachers'])->name('get.available.fixed.teachers');
Route::post('available/preferred-teachers', [SemesterTimetableHelperController::class, 'getAvailableTeachersPref'])->name('get.available.prefered.teachers');
Route::post('/generate-slots', [SemesterTimetableHelperController::class, 'generateSlots'])->name('get.available.prefered.teachers');
Route::post('/available-halls', [SemesterTimetableHelperController::class, 'getAvailableHalls'])->name('get.available.prefered.teachers');
Route::post('/courses', [SemesterTimetableHelperController::class, 'getCourses'])->name('get.courses.schoolSemester');
