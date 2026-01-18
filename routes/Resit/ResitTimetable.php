<?php


use App\Http\Controllers\ResitTimetable\ResitExamTimetable;
use Illuminate\Support\Facades\Route;

Route::get('/specialties/{specialtyId}/resit-exam/{examId}/timetable', [ResitExamTimetable::class, 'getResitsBySpecialty'])
->name('specialties.resit-exams.timetable');

Route::post('/resit-exam/{resitExamId}/timetable', [ResitExamTimetable::class, 'createResitTimetable'])
->name('resit-exams.timetable.store');

Route::get('/resit-exam/{resitExamId}/courses', [ResitExamTimetable::class, 'getResitCoursesByExam'])
->name('resit-exams.courses.index');


Route::delete('/resit-exam/{resitExamId}/timetable', [ResitExamTimetable::class, 'deleteResitTimetable'])
->name('resit-exams.timetable.destroy');


Route::put('/resit-exam/{resitExamId}/timetable', [ResitExamTimetable::class, 'updateResitTimetable'])
->name('resit-exams.timetable.update');

Route::post('/auto-gen-timetable', [ResitExamTimetable::class, 'autoGenerateResitExamTimetable'])->name('auto-generate.resit-exam-timetable');

