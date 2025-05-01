<?php
use illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\SemesterController;

Route::middleware(['auth:sanctum'])->post('/create-semester', [SemesterController::class, 'createSemester']);
Route::middleware(['auth:sanctum'])->delete('/delete-semester/{semester_id}', [SemesterController::class, 'deleteSemester']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/semesters', [SemesterController::class, 'getSemesters']);
Route::middleware(['auth:sanctum'])->put('/update-semester/{semester_id}', [SemesterController::class, 'updateSemester']);
