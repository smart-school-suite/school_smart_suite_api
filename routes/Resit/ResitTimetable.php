<?php


use App\Http\Controllers\ResitTimetable\ResitExamTimetable;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:"schoolAdmin.timetable.resitexam.view'])->get('/specialties/{specialtyId}/resit-exam/{examId}/timetable', [ResitExamTimetable::class, 'getResitsBySpecialty'])
->name('specialties.resit-exams.timetable');

// Create a resit timetable for a specific resit exam
Route::middleware(['permission:schoolAdmin.timetable.resitexam.create'])->post('/resit-exam/{resitExamId}/timetable', [ResitExamTimetable::class, 'createResitTimetable'])
->name('resit-exams.timetable.store');

// Get resit courses for a specific resit exam
Route::middleware(['permission:schoolAdmin.timetable.resitexam.courses.view'])->get('/resit-exam/{resitExamId}/courses', [ResitExamTimetable::class, 'getResitCoursesByExam'])
->name('resit-exams.courses.index');

// Delete the resit timetable for a specific resit exam
Route::middleware(['permission:schoolAdmin.timetable.resitexam.delete'])->delete('/resit-exam/{resitExamId}/timetable', [ResitExamTimetable::class, 'deleteResitTimetable'])
->name('resit-exams.timetable.destroy');

// Update the resit timetable for a specific resit exam
Route::middleware(['permission:schoolAdmin.timetable.resitexam.update'])->put('/resit-exam/{resitExamId}/timetable', [ResitExamTimetable::class, 'updateResitTimetable'])
->name('resit-exams.timetable.update');

Route::post('/auto-gen-timetable', [ResitExamTimetable::class, 'autoGenerateResitExamTimetable'])->name('auto-generate.resit-exam-timetable');

