<?php

use App\Http\Controllers\Course\CourseTypeController;
use Illuminate\Support\Facades\Route;

Route::post("/create", [CourseTypeController::class, "createCourseType"])->name("create.CourseType");
Route::put("/update/{courseTypeId}", [CourseTypeController::class, "updateCourseType"])->name("update.CourseType");
Route::delete("/delete/{courseTypeId}", [CourseTypeController::class, "deleteCourseType"])->name("delete.CourseType");
Route::get("/active", [CourseTypeController::class, "getActiveCourseTypes"])->name("active.CourseTypes");
Route::get("/", [CourseTypeController::class, "getAllCourseTypes"])->name("CourseTypes");
Route::post("/deactivate/{courseTypeId}", [CourseTypeController::class, "deactivateCourseType"])->name("deactivate.CourseType");
Route::post("/activate/{courseTypeId}", [CourseTypeController::class, "activateCourseType"])->name("activate.CourseType");
