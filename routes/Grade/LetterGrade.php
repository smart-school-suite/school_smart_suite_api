<?php

use App\Http\Controllers\LetterGradecontroller;
use Illuminate\Support\Facades\Route;

// Create a new letter grade
Route::middleware(['permission:appAdmin.letterGrade.create'])->post('/letter-grades', [LetterGradeController::class, 'createLettGrade'])
    ->name('letter-grades.store');

// Get all letter grades
Route::get('/letter-grades', [LetterGradeController::class, 'getLetterGrades'])
    ->name('letter-grades.index');

// Delete a specific letter grade
Route::middleware(['permission:appAdmin.letterGrade.delete'])->delete('/letter-grades/{letterGradeId}', [LetterGradeController::class, 'deleteLetterGrade'])
    ->name('letter-grades.destroy');

// Update a specific letter grade
Route::middleware(['permission:appAdmin.letterGrade.update'])->put('/letter-grades/{letterGradeId}', [LetterGradeController::class, 'updateLetterGrade'])
    ->name('letter-grades.update');
