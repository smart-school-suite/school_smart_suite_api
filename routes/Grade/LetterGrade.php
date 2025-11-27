<?php

use App\Http\Controllers\LetterGrade\LetterGradeController;
use Illuminate\Support\Facades\Route;

// Create a new letter grade
Route::middleware(['permission:appAdmin.letterGrade.create'])->post('/', [LetterGradeController::class, 'createLettGrade'])
    ->name('letter-grades.store');

// Get all letter grades
Route::get('/', [LetterGradeController::class, 'getLetterGrades'])
    ->name('letter-grades.index');

// Delete a specific letter grade
Route::middleware(['permission:appAdmin.letterGrade.delete'])->delete('/{letterGradeId}', [LetterGradeController::class, 'deleteLetterGrade'])
    ->name('letter-grades.destroy');

// Update a specific letter grade
Route::middleware(['permission:appAdmin.letterGrade.update'])->put('/{letterGradeId}', [LetterGradeController::class, 'updateLetterGrade'])
    ->name('letter-grades.update');
