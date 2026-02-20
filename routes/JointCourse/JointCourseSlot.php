<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JointCourse\JointCourseSlotController;

Route::post('/preference/create', [JointCourseSlotController::class, 'createPreferenceJointCourseSlot'])
    ->name('joint-course-slots.preference.store');

Route::post('/fixed/create', [JointCourseSlotController::class, 'createFixedJointCourseSlot'])
    ->name('joint-course-slots.fixed.store');

Route::get('/semester-joint-course/{semesterJointCourseId}', [JointCourseSlotController::class, 'getJointCourseSlotsForSemester'])
    ->name('joint-course-slots.semester-joint-course');

Route::post('/suggested-slots', [JointCourseSlotController::class, 'suggestJointCourseSlots'])
    ->name('joint-course-slots.suggested-slots');

Route::delete('/{jointCourseSlotId}/delete', [JointCourseSlotController::class, 'deleteJointCourseSlotSlotId'])
    ->name('joint-course-slots.delete');

Route::put('/update-fixed-slots', [JointCourseSlotController::class, 'updateFixedJointCourseSlots'])
    ->name('joint-course-slots.update-fixed-slots');

Route::put('/update-preference-slots', [JointCourseSlotController::class, 'updatePreferenceJointCourseSlots'])
    ->name('joint-course-slots.update-preference-slots');
