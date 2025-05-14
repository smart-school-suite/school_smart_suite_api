<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessedStudentController;

// Get all exam candidates students
Route::middleware(['permission:schoolAdmin.exam.candidate.view'])->get('/exam-candidates', [AccessedStudentController::class, 'getAccessedStudent'])
    ->name('exam-candidates.index');

// Delete a specific accessed student record
Route::middleware(['permission:schoolAdmin.exam.candidate.delete'])->delete('/exam-candidates/{accessedStudentId}', [AccessedStudentController::class, 'deleteAccessedStudent'])
    ->name('exam-candidates.destroy');
