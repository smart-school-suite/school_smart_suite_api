<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamTypecontroller;
use App\Http\Middleware\IdentifyTenant;

// Create a new exam type
Route::post('/exam-types', [ExamTypeController::class, 'createExamType'])
    ->name('exam-types.store');

// Delete a specific exam type
Route::delete('/exam-types/{examTypeId}', [ExamTypeController::class, 'deleteExamType'])
    ->name('exam-types.destroy');

// Update a specific exam type
Route::put('/exam-types/{examTypeId}', [ExamTypeController::class, 'updateExamType'])
    ->name('exam-types.update');

// Get all exam types (tenant identification middleware applied)
Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/exam-types', [ExamTypeController::class, 'getExamType'])
    ->name('exam-types.index');
