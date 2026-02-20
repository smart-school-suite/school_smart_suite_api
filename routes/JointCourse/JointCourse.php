<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JointCourse\JointCourseController;

Route::post('/create', [JointCourseController::class, 'createJointCourse'])
    ->name('joint-courses.store');

Route::put('/update/{jointCourseId}', [JointCourseController::class, 'updateJointCourse'])
    ->name('joint-courses.update');

Route::delete('/delete/{jointCourseId}', [JointCourseController::class, 'deleteJointCourse'])
    ->name('joint-courses.destroy');

Route::get('/details/{jointCourseId}', [JointCourseController::class, 'getJointCourseDetails'])
    ->name('joint-courses.show');

Route::get('/', [JointCourseController::class, 'getJointCourses'])
    ->name('joint-courses.index');
