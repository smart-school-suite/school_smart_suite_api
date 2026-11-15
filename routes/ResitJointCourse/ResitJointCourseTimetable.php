<?php

use App\Http\Controllers\ResitJointCourse\ResitJointCourseTimetableController as Joint;
use Illuminate\Support\Facades\Route;

// Prefix: /api/v1/resit-joint-course-timetable (same routes as the exam joint course timetable)
Route::get('/terms', [Joint::class, 'getTerms'])->name('resitJointCourseTimetable.terms');
Route::get('/term/{schoolYearId}/{examTypeId}', [Joint::class, 'getTerm'])->name('resitJointCourseTimetable.term');
Route::post('/resit-exam/{resitExamId}/sync', [Joint::class, 'syncResitExam'])->name('resitJointCourseTimetable.resitExam.sync');
Route::get('/{jointCourseId}', [Joint::class, 'getJointCourse'])->name('resitJointCourseTimetable.show');
Route::post('/{jointCourseId}/generate', [Joint::class, 'generateSlots'])->name('resitJointCourseTimetable.generate');
Route::post('/{jointCourseId}/proposals/{proposalId}/confirm', [Joint::class, 'confirmSlot'])->name('resitJointCourseTimetable.proposals.confirm');
Route::put('/{jointCourseId}/slot', [Joint::class, 'saveSlot'])->name('resitJointCourseTimetable.slot.save');
Route::delete('/{jointCourseId}/slot', [Joint::class, 'removeSlot'])->name('resitJointCourseTimetable.slot.remove');
