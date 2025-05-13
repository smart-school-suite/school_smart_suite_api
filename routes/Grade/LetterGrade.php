<?php

use App\Http\Controllers\LetterGradecontroller;
use Illuminate\Support\Facades\Route;

// Create a new letter grade
Route::post('/letter-grades', [LetterGradeController::class, 'createLettGrade'])
    ->name('letter-grades.store');

// Get all letter grades
Route::get('/letter-grades', [LetterGradeController::class, 'getLetterGrades'])
    ->name('letter-grades.index');

// Delete a specific letter grade
Route::delete('/letter-grades/{letterGradeId}', [LetterGradeController::class, 'deleteLetterGrade'])
    ->name('letter-grades.destroy');

// Update a specific letter grade
Route::put('/letter-grades/{letterGradeId}', [LetterGradeController::class, 'updateLetterGrade'])
    ->name('letter-grades.update');
