<?php


use App\Http\Controllers\ResitTimeTableController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:"schoolAdmin.timetable.resitexam.view'])->get('/specialties/{specialtyId}/resit-exam/{examId}/timetable', [ResitTimeTableController::class, 'getResitsBySpecialty'])
->name('specialties.resit-exams.timetable');

// Create a resit timetable for a specific resit exam
Route::middleware(['permission:schoolAdmin.timetable.resitexam.create'])->post('/resit-exam/{resitExamId}/timetable', [ResitTimeTableController::class, 'createResitTimetable'])
->name('resit-exams.timetable.store');

// Get resit courses for a specific resit exam
Route::middleware(['permission:schoolAdmin.timetable.resitexam.courses.view'])->get('/resit-exam/{resitExamId}/courses', [ResitTimeTableController::class, 'getResitCoursesByExam'])
->name('resit-exams.courses.index');

// Delete the resit timetable for a specific resit exam
Route::middleware(['permission:schoolAdmin.timetable.resitexam.delete'])->delete('/resit-exam/{resitExamId}/timetable', [ResitTimeTableController::class, 'deleteResitTimetable'])
->name('resit-exams.timetable.destroy');

// Update the resit timetable for a specific resit exam
Route::middleware(['permission:schoolAdmin.timetable.resitexam.update'])->put('/resit-exam/{resitExamId}/timetable', [ResitTimeTableController::class, 'updateResitTimetable'])
->name('resit-exams.timetable.update');


