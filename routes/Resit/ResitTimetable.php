<?php
use App\Http\Controllers\ResitTimeTableController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/get-specialty-resit/{specialty_id}/{exam_id}', [ResitTimeTableController::class, 'getResitsBySpecialty']);
Route::middleware(['auth:sanctum'])->post('/createResitTimetable/{resitExamId}', [ResitTimeTableController::class, 'createResitTimetable']);
Route::middleware(['auth:sanctum'])->get('/getResitCoursesByExam/{resitExamId}', [ResitTimeTableController::class, 'getResitCoursesByExam']);
Route::middleware(['auth:sanctum'])->delete('/deleteResitTimetable/{resitExamId}', [ResitTimetableController::class, 'deleteResitTimetable']);
Route::middleware(['auth:sanctum'])->put('/updateResitTimetable/{resitExamId}', [ResitTimetableController::class, 'updateResitTimetable']);
