<?php
use App\Http\Controllers\ResitExam\ResitCandidateController;
use Illuminate\Support\Facades\Route;

Route::delete('/{candidateId}', [ResitCandidateController::class, 'deleteResitCandidate'])->name('delete-resit-candidate');
Route::get('/', [ResitCandidateController::class, 'getResitCandidates'])->name('resit-candidates');
