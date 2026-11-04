<?php

use App\Http\Controllers\JointCourse\JointCourseTimetableController;
use Illuminate\Support\Facades\Route;

Route::get('/terms', [JointCourseTimetableController::class, 'getTerms'])->name('jointCourseTimetable.terms');
Route::get('/term/{schoolYearId}/{semesterId}', [JointCourseTimetableController::class, 'getTerm'])->name('jointCourseTimetable.term');
Route::put('/{semesterJointCourseId}/slots', [JointCourseTimetableController::class, 'saveSlots'])->name('jointCourseTimetable.slots.save');
Route::post('/{semesterJointCourseId}/recommended-slots', [JointCourseTimetableController::class, 'getRecommendedSlots'])->name('jointCourseTimetable.slots.recommended');
