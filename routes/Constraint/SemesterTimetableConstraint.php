<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Constraint\SemesterTimetableConstraintController;

Route::get('/', [SemesterTimetableConstraintController::class, 'getAllSemesterTimetableConstraint'])->name('semesterTimetableConstraints.get');
Route::get('/{constraintId}/details', [SemesterTimetableConstraintController::class, 'getSemesterTimetableConstraintById'])->name('semesterTimetableConstraints.getById');
Route::post('/{constraintId}/deactivate', [SemesterTimetableConstraintController::class, 'deactivateSemesterTimetableConstraint'])->name('semesterTimetableConstraints.deactivate');
Route::post('/{constraintId}/activate', [SemesterTimetableConstraintController::class, 'activateSemesterTimetableConstraint'])->name('semesterTimetableConstraints.activate');
