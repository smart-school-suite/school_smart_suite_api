<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamTimeTableController;

// Create a new exam timetable for a specific exam
Route::middleware(['permission:schoolAdmin.exam.timetable.create'])->post('/exams/{examId}/timetable', [ExamTimeTableController::class, 'createTimtable'])
    ->name('exams.timetable.store');

// Update the exam timetable (requires request body to identify which entry to update)
Route::middleware(['permission:schoolAdmin.exam.timetable.update'])->put('/exam-timetable', [ExamTimeTableController::class, 'updateTimetable'])
    ->name('exam-timetable.update');

// Get exam timetable by level and specialty
Route::middleware(['permission:schoolAdmin.exam.timetable.view'])->get('/levels/{levelId}/specialties/{specialtyId}/exam-timetable', [ExamTimeTableController::class, 'getTimetableBySpecialty'])
    ->name('levels.specialties.exam-timetable.index');

// Get course data for preparing an exam timetable
Route::middleware(['permission:schoolAdmin.exam.timetable.course.data'])->get('/exams/{examId}/timetable/data', [ExamTimeTableController::class, 'prepareExamTimeTableData'])
    ->name('exams.timetable.data');

// Delete a specific entry from the exam timetable
Route::middleware(['permission:schoolAdmin.exam.timetable.delete'])->delete('/exam-timetable/entries/{entryId}', [ExamTimeTableController::class, 'deleteTimetableEntry'])
    ->name('exam-timetable.entries.destroy');

// Delete the entire exam timetable for a specific exam
Route::middleware(['permission:schoolAdmin.exam.timetable.delete'])->delete('/exams/{examId}/timetable', [ExamTimeTableController::class, 'deleteTimetable'])
    ->name('exams.timetable.destroy');
