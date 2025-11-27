<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamCandidateController;
// Get all exam candidates students
Route::middleware(['permission:schoolAdmin.exam.candidate.view'])->get('/', [ExamCandidateController::class, 'getAccessedStudent'])
    ->name('exam-candidates.index');

// Delete a specific accessed student record
Route::middleware(['permission:schoolAdmin.exam.candidate.delete'])->delete('/{candidateId}', [ExamCandidateController::class, 'deleteAccessedStudent'])
    ->name('exam-candidates.destroy');
