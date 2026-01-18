<?php

use App\Http\Controllers\LetterGrade\LetterGradeController;
use Illuminate\Support\Facades\Route;

Route::post('/', [LetterGradeController::class, 'createLettGrade'])
    ->name('letter-grades.store');

Route::get('/', [LetterGradeController::class, 'getLetterGrades'])
    ->name('letter-grades.index');

Route::delete('/{letterGradeId}', [LetterGradeController::class, 'deleteLetterGrade'])
    ->name('letter-grades.destroy');

Route::put('/{letterGradeId}', [LetterGradeController::class, 'updateLetterGrade'])
    ->name('letter-grades.update');
