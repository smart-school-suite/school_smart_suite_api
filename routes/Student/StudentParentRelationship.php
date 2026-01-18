<?php

use App\Http\Controllers\Student\StudentParentRelationshipController;
use Illuminate\Support\Facades\Route;

Route::get("/active", [StudentParentRelationshipController::class, 'getActiveStudentParentRelationships'])->name("active.studentParentRelationship");
Route::get("/", [StudentParentRelationshipController::class, 'getAllStudentParentRelationship'])->name("studentParentRelationship");
