<?php

use App\Http\Controllers\LetterGradecontroller;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->post('/create-letter-grade', [LetterGradecontroller::class, 'createLettGrade']);
Route::middleware(['auth:sanctum'])->get('/get-letter-grades', [LetterGradecontroller::class, 'getLetterGrades']);
Route::middleware(['auth:sanctum'])->delete('/delete-letter-grade/{letter_grade_id}', [LetterGradecontroller::class, 'deleteLetterGrade']);
Route::middleware(['auth:sanctum'])->put('/update-letter-grate/{letter_grade_id}', [LetterGradecontroller::class, 'updateLetterGrade']);
