<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemTimetableConstraint\ConstraintTypeController;

Route::get('/', [ConstraintTypeController::class, 'getConstraintTypes'])->name("getConstraintTypes");
Route::get('/{constraintTypeId}', [ConstraintTypeController::class, 'getConstraintTypeById'])->name("getConstraintTypeById");
Route::post('/{constraintTypeId}/activate', [ConstraintTypeController::class, 'activateConstraintType'])->name("activateConstraintType");
Route::post('/{constraintTypeId}/deactivate', [ConstraintTypeController::class, 'deactivateConstraintType'])->name("deactivateConstraintType");
