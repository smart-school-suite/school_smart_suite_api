<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamTimeTableController;

// Create a new exam timetable for a specific exam
Route::post('/exams/{examId}/timetable', [ExamTimeTableController::class, 'createTimtable'])
    ->name('exams.timetable.store');

// Update the exam timetable (requires request body to identify which entry to update)
Route::put('/exam-timetable', [ExamTimeTableController::class, 'updateTimetable'])
    ->name('exam-timetable.update');

// Get exam timetable by level and specialty
Route::get('/levels/{levelId}/specialties/{specialtyId}/exam-timetable', [ExamTimeTableController::class, 'getTimetableBySpecialty'])
    ->name('levels.specialties.exam-timetable.index');

// Get course data for preparing an exam timetable
Route::get('/exams/{examId}/timetable/data', [ExamTimeTableController::class, 'prepareExamTimeTableData'])
    ->name('exams.timetable.data');

// Delete a specific entry from the exam timetable
Route::delete('/exam-timetable/entries/{entryId}', [ExamTimeTableController::class, 'deleteTimetableEntry'])
    ->name('exam-timetable.entries.destroy');

// Delete the entire exam timetable for a specific exam
Route::delete('/exams/{examId}/timetable', [ExamTimeTableController::class, 'deleteTimetable'])
    ->name('exams.timetable.destroy');
