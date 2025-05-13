<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessedStudentController;

// Get all exam candidates students
Route::get('/exam-candidates', [AccessedStudentController::class, 'getAccessedStudent'])
    ->name('exam-candidates.index');

// Delete a specific accessed student record
Route::delete('/exam-candidates/{accessedStudentId}', [AccessedStudentController::class, 'deleteAccessedStudent'])
    ->name('exam-candidates.destroy');
