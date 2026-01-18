<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamCandidateController;


Route::get('/', [ExamCandidateController::class, 'getAccessedStudent'])
    ->name('exam-candidates.index');

Route::delete('/{candidateId}', [ExamCandidateController::class, 'deleteAccessedStudent'])
    ->name('exam-candidates.destroy');
