<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JointCourse\SemesterJointCourseController;


Route::get("/", [SemesterJointCourseController::class, 'getSemesterJointCourses'])->name("get.semesterJointCourses");
