<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamTypecontroller;
use App\Http\Middleware\IdentifyTenant;

// Create a new exam type
Route::middleware(['permission:appAdmin.examType.create'])->post('/', [ExamTypeController::class, 'createExamType'])
    ->name('exam-types.store');

// Delete a specific exam type
Route::middleware(['permission:appAdmin.examType.delete'])->delete('/{examTypeId}', [ExamTypeController::class, 'deleteExamType'])
    ->name('exam-types.destroy');

// Update a specific exam type
Route::middleware(['permission:appAdmin.examType.update'])->put('/{examTypeId}', [ExamTypeController::class, 'updateExamType'])
    ->name('exam-types.update');

// Get all exam types (tenant identification middleware applied)
Route::middleware(['permission:schoolAdmin.examType.view|appAdmin.examType.view'])->middleware(['auth:sanctum', IdentifyTenant::class])->get('/', [ExamTypeController::class, 'getExamType'])
    ->name('exam-types.index');
