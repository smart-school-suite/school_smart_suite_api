<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Constraint\ConstraintTypeController;

Route::get('/', [ConstraintTypeController::class, 'getAllConstraintTypes'])->name('constraintTypes.get');
Route::get('/{constraintTypeId}/details', [ConstraintTypeController::class, 'getConstraintTypeById'])->name('constraintTypes.getById');
Route::post('/{constraintTypeId}/deactivate', [ConstraintTypeController::class, 'deactivateConstraintType'])->name('constraintTypes.deactivate');
Route::post('/{constraintTypeId}/activate', [ConstraintTypeController::class, 'activateConstraintType'])->name('constraintTypes.activate');
