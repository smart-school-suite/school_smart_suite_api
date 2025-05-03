<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamTimeTableController;

Route::middleware(['auth:sanctum'])->post('/create-timetable/{examId}', [ExamTimeTableController::class, 'createTimtable']);
Route::middleware(['auth:sanctum'])->put('/update-examtimetable', [ExamTimeTableController::class, 'updateTimetable']);
Route::middleware(['auth:sanctum'])->get('/generate-timetable/{levelId}/{specialtyId}', [ExamTimeTableController::class, 'getTimetableBySpecialty']);
Route::middleware(['auth:sanctum'])->get('/get-course-data/{examId}', [ExamTimeTableController::class, 'prepareExamTimeTableData']);
Route::middleware(['auth:sanctum'])->delete('/deleteTimetableEntry/{entryId}', [ExamTimeTableController::class, 'deleteTimetableEntry']);
Route::middleware(['auth:sanctum'])->delete('/deleteTimeTable/{examId}', [ExamTimetableController::class, 'deleteTimetable']);
