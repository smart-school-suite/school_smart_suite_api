<?php
use illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\Semester\SemesterController;

// Create a new semester
Route::middleware(['permission:appAdmin.semester.create'])->post('/', [SemesterController::class, 'createSemester'])
->name('semesters.store');

// Delete a specific semester
Route::middleware(['permission:appAdmin.semester.delete'])->delete('/{semesterId}', [SemesterController::class, 'deleteSemester'])
->name('semesters.destroy');

// Update a specific semester
Route::middleware(['permission:appAdmin.semester.update'])->put('/{semesterId}', [SemesterController::class, 'updateSemester'])
->name('semesters.update');

Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/', [SemesterController::class, 'getSemesters'])
    ->name('semesters.index');
