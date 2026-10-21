<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Graduation\AlumniController;

Route::get("/", [AlumniController::class, 'getAlumni'])->name("get.alumni");
Route::post("/{alumusId}/reinstate", [AlumniController::class, 'reinstateAlumus'])->name("reinstate.alumnus");
Route::get("/{alumusId}/details", [AlumniController::class, 'getAlumusDetails'])->name("get.alumusDetails");
