<?php

use App\Http\Controllers\ExamJointCourse\ExamJointCourseTimetableController;
use Illuminate\Support\Facades\Route;

Route::get('/terms', [ExamJointCourseTimetableController::class, 'getTerms'])->name('examJointCourseTimetable.terms');
Route::get('/term/{schoolYearId}/{examTypeId}', [ExamJointCourseTimetableController::class, 'getTerm'])->name('examJointCourseTimetable.term');
Route::post('/exam/{examId}/sync', [ExamJointCourseTimetableController::class, 'syncExam'])->name('examJointCourseTimetable.exam.sync');
Route::get('/{examJointCourseId}', [ExamJointCourseTimetableController::class, 'getJointCourse'])->name('examJointCourseTimetable.show');
Route::post('/{examJointCourseId}/generate', [ExamJointCourseTimetableController::class, 'generateSlots'])->name('examJointCourseTimetable.generate');
Route::post('/{examJointCourseId}/proposals/{proposalId}/confirm', [ExamJointCourseTimetableController::class, 'confirmSlot'])->name('examJointCourseTimetable.proposals.confirm');
Route::put('/{examJointCourseId}/slot', [ExamJointCourseTimetableController::class, 'saveSlot'])->name('examJointCourseTimetable.slot.save');
Route::delete('/{examJointCourseId}/slot', [ExamJointCourseTimetableController::class, 'removeSlot'])->name('examJointCourseTimetable.slot.remove');
