<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\TeacherSpecailtyPreferenceController;

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get("/teacher-specailty-preference/{teacherId}", [TeacherSpecailtyPreferenceController::class, 'getTeacherSpecailtyPreference']);
