<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Audience\AudienceController;


Route::get("/summary", [AudienceController::class, 'getSchoolAudienceSummary'])->name("audience.summary");
Route::get("/school-admin", [AudienceController::class, 'getSchoolAdminAudience'])->name("schoolAdmin.audience");
Route::get("/teacher/level", [AudienceController::class, 'getTeacherAudienceByLevel'])->name("teacher.level.audience");
Route::get("/teacher/specialty", [AudienceController::class, 'getTeacherAudienceBySpecialty'])->name("teacher.specialty.audience");
Route::get("/teacher/department", [AudienceController::class, 'getTeacherAudienceByDepartment'])->name("teacher.department.audience");
Route::get("/student", [AudienceController::class, 'getStudentAudience'])->name("student.audience");
Route::get("/student/department", [AudienceController::class, 'getStudentAudienceByDepartment'])->name("student.department.audience");
Route::get("/student/level", [AudienceController::class, 'getStudentAudienceByLevel'])->name("student.level.audience");
Route::get("/student/specialty", [AudienceController::class, 'getStudentAudienceBySpecialty'])->name("student.specialty.audience");
Route::get("/teacher", [AudienceController::class, "getTeacherAudience"])->name("teacher.audience");
