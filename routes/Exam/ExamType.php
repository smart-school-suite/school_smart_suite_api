<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamTypecontroller;
use App\Http\Middleware\IdentifyTenant;

// Create a new exam type
Route::middleware(['permission:appAdmin.examType.create'])->post('/exam-types', [ExamTypeController::class, 'createExamType'])
    ->name('exam-types.store');

// Delete a specific exam type
Route::middleware(['permission:appAdmin.examType.delete'])->delete('/exam-types/{examTypeId}', [ExamTypeController::class, 'deleteExamType'])
    ->name('exam-types.destroy');

// Update a specific exam type
Route::middleware(['permission:appAdmin.examType.update'])->put('/exam-types/{examTypeId}', [ExamTypeController::class, 'updateExamType'])
    ->name('exam-types.update');

// Get all exam types (tenant identification middleware applied)
Route::middleware(['permission:schoolAdmin.examType.view|appAdmin.examType.view'])->middleware(['auth:sanctum', IdentifyTenant::class])->get('/exam-types', [ExamTypeController::class, 'getExamType'])
    ->name('exam-types.index');
