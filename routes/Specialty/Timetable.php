<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeTableController;

Route::middleware(['auth:sanctum'])->post('/createTimetableByAvailability/{semesterId}', [TimeTableController::class, 'createTimetableByAvailability']);
Route::middleware(['auth:sanctum'])->post('/createTimetable/{semesterId}', [TimeTableController::class, 'createTimetable']);
Route::middleware(['auth:sanctum'])->put('/update-timetable/{timetable_id}', [TimeTableController::class, 'updateTimetable']);
Route::middleware(['auth:sanctum'])->delete('/delete-timetable/{timetable_id}', [TimeTableController::class, 'deleteTimetable']);
Route::middleware(['auth:sanctum'])->get('/generate-timetable', [TimeTableController::class, 'generateTimetable']);
Route::middleware(['auth:sanctum'])->get('/timetable-details/{entry_id}', [TimeTableController::class, 'getTimetableDetails']);
Route::middleware(['auth:sanctum'])->get('/instructor-availability/{semester_id}/{specialty_id}', [TimetableController::class, 'getInstructorAvailabilityBySemesterSpecialty']);
