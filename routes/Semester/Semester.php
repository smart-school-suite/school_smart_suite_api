<?php
use illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\Semester\SemesterController;

Route::post('/', [SemesterController::class, 'createSemester'])
->name('semesters.store');

Route::delete('/{semesterId}', [SemesterController::class, 'deleteSemester'])
->name('semesters.destroy');

Route::put('/{semesterId}', [SemesterController::class, 'updateSemester'])
->name('semesters.update');

Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/', [SemesterController::class, 'getSemesters'])
    ->name('semesters.index');
