<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamTimeTableController;

Route::middleware(['auth:sanctum'])->post('/create-timetable/{examId}', [ExamTimeTableController::class, 'createTimtable']);
Route::middleware(['auth:sanctum'])->put('/update-exam-time-table/{examtimetable_id}', [ExamTimeTableController::class, 'updateTimetable']);
Route::middleware(['auth:sanctum'])->get('/generate-timetable/{level_id}/{specialty_id}', [ExamTimeTableController::class, 'getTimetableBySpecialty']);
Route::middleware(['auth:sanctum'])->get('/course-data/{exam_id}', [ExamTimeTableController::class, 'prepareExamTimeTableData']);
Route::middleware(['auth:sanctum'])->delete('/deleteTimetableEntry/{timetableEntryId}', [ExamTimeTableController::class, 'deleteTimetableEntry']);
Route::middleware(['auth:sanctum'])->delete('/deleteTimeTable/{examId}', [ExamTimetableController::class, 'deleteTimetable']);
