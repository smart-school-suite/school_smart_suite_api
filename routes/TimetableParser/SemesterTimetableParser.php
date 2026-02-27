<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimetableParser\SemesterTimetableParserController;

Route::post('/interpret', [SemesterTimetableParserController::class, 'interpret'])->name('semesterTimetableParser.interpret');
