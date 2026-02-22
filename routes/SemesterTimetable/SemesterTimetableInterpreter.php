<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterTimetable\SemesterTimetableResponseInterpreter;

Route::get('/interpret', [SemesterTimetableResponseInterpreter::class, 'interpret'])->name('semesterTimetable.response.interpret');
