<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemTimetableConstraint\ConstraintController;

Route::get('/category', [ConstraintController::class, 'getConstraintsByCategory'])->name("getConstraintsByCategory");
Route::get('/all', [ConstraintController::class, 'getAllConstraints'])->name("getAllConstraints");
Route::get('/{constraintId}', [ConstraintController::class, 'getConstraintById'])->name("getConstraintById");
