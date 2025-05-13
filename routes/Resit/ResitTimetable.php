<?php
use App\Http\Controllers\ResitTimeTableController;
use Illuminate\Support\Facades\Route;

Route::get('/specialties/{specialtyId}/resit-exams/{examId}/timetable', [ResitTimeTableController::class, 'getResitsBySpecialty'])
->name('specialties.resit-exams.timetable');

// Create a resit timetable for a specific resit exam
Route::post('/resit-exams/{resitExamId}/timetable', [ResitTimeTableController::class, 'createResitTimetable'])
->name('resit-exams.timetable.store');

// Get resit courses for a specific resit exam
Route::get('/resit-exams/{resitExamId}/courses', [ResitTimeTableController::class, 'getResitCoursesByExam'])
->name('resit-exams.courses.index');

// Delete the resit timetable for a specific resit exam
Route::delete('/resit-exams/{resitExamId}/timetable', [ResitTimeTableController::class, 'deleteResitTimetable'])
->name('resit-exams.timetable.destroy');

// Update the resit timetable for a specific resit exam
Route::put('/resit-exams/{resitExamId}/timetable', [ResitTimeTableController::class, 'updateResitTimetable'])
->name('resit-exams.timetable.update');
