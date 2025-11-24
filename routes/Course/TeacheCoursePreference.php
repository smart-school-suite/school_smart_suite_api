<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Course\TeacherCoursePreferenceController;

Route::post('/assign', [TeacherCoursePreferenceController::class, 'assignTeacherToCourse'])->name("assign.course.to.teacher");
Route::get("/course/assignable/teacher/{teacherId}", [TeacherCoursePreferenceController::class, "getAssignableTeacherCourses"])->name("get.assignable.course.preferences");
Route::delete("/remove", [TeacherCoursePreferenceController::class, "removeTeacherAssignedCourse"])->name("remove.teacher.course.preference");
Route::get("/course/assigned/teacher/{teacherId}", [TeacherCoursePreferenceController::class, "removeTeacherAssignedCourse"])->name("get.assigned.teacher.courses");
