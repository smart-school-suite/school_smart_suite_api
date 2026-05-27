<?php

use App\Http\Controllers\ExamTimetable\ExamTimetableVersionController;
use Illuminate\Support\Facades\Route;


Route::post('/create', [ExamTimetableVersionController::class, 'createVersion'])->name("examTimetableVersion.create");
Route::get('/exam/{examId}', [ExamTimetableVersionController::class, 'getVersionsExamId'])->name("examTimetableVersion.get");
Route::delete('/delete/{versionId}', [ExamTimetableVersionController::class, 'deleteVersion'])->name("examTimetableVersion.delete");
