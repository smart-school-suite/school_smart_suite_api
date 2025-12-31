<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentSourceController;

Route::post("/create", [StudentSourceController::class, "createStudentSource"])->name("create.StudentSource");
Route::post("{studentSourceId}/update", [StudentSourceController::class, "updateStudentSource"])->name("update.StudentSource");
Route::delete("{studentSourceId}/delete", [StudentSourceController::class, "deleteStudentSource"])->name("delete.StudentSource");
Route::post("{studentSourceId}/activate", [StudentSourceController::class, "activateStudentSource"])->name("activate.StudentSource");
Route::post("{studentSourceId}/deactivate", [StudentSourceController::class, "deactivateStudentSource"])->name("deactivate.StudentSource");
Route::post("/active", [StudentSourceController::class, "getActiveStudentSource"])->name("get.StudentSource");
Route::get("{studentSourceId}/details", [StudentSourceController::class, "getStudentSourceDetails"])->name("get.StudentSource.details");
Route::get("/", [StudentSourceController::class, "getAllStudentSource"])->name("get.StudentSource");
