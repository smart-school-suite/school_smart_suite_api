<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeTableController;

Route::middleware(['auth:sanctum'])->post('/createTimetableByAvailability/{semesterId}', [TimeTableController::class, 'createTimetableByAvailability']);
Route::middleware(['auth:sanctum'])->post('/createTimetable/{semesterId}', [TimeTableController::class, 'createTimetable']);
Route::middleware(['auth:sanctum'])->put('/update-timetable/{timetable_id}', [TimeTableController::class, 'updateTimetable']);
Route::middleware(['auth:sanctum'])->put('/update-timetable-by-availability', [TimeTableController::class, 'updateTimetableByTeacherAvailability']);
Route::middleware(['auth:sanctum'])->delete('/delete-timetable/{studentBatchId}/{specialtyId}/{levelId}/{semesterId}', [TimeTableController::class, 'deleteTimetable']);
Route::middleware(['auth:sanctum'])->get('/generate-timetable', [TimeTableController::class, 'generateTimetable']);
Route::middleware(['auth:sanctum'])->delete('/delete-timetable-entry/{entryId}', [TimeTableController::class, 'deleteTimeTableEntry']);
Route::middleware(['auth:sanctum'])->get('/timetable-details/{entryId}', [TimeTableController::class, 'getTimetableDetails']);
Route::middleware(['auth:sanctum'])->get('/instructor-availability/{semesterId}/{specialtyId}', [TimetableController::class, 'getInstructorAvailabilityBySemesterSpecialty']);
