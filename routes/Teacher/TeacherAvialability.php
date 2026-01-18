<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\TeacherPreferedTeachingTimeController;

Route::post('/create', [TeacherPreferedTeachingTimeController::class, 'createInstructorAvailability'])
    ->name('instructor-availability.create');

Route::post('/{targetAvailabilityId}/{availabilityId}/create',
  [TeacherPreferedTeachingTimeController::class, 'createAvailabilityByOtherSlots']
)->name('instructor-availability.create');

Route::get('/teacher/{teacherId}/availability', [TeacherPreferedTeachingTimeController::class, 'getInstructorAvailabilitesByTeacher'])
    ->name('teachers.availability.index');

Route::patch('/update', [TeacherPreferedTeachingTimeController::class, 'bulkUpdateInstructorAvialabililty'])
       ->name('instructor-availability.bulk-update');

Route::get('/school-semesters/teacher/{teacherId}/specialty-preference', [TeacherPreferedTeachingTimeController::class, 'getSchoolSemestersByTeacherSpecialtyPreference']);

Route::delete('/instructor-availability/teacher/{teacherId}/availability/{availabilityId}', [TeacherPreferedTeachingTimeController::class, 'deleteAvailabilitySlots'])
    ->name('instructor-availability.delete-all-by-teacher-availability');

Route::get('/instructor-availabilities', [TeacherPreferedTeachingTimeController::class, 'getInstructorAvailabilities'])->name('instructor-availability.get');

Route::get('/instructor-availability/{availabilityId}', [TeacherPreferedTeachingTimeController::class, 'getInstructorAvailabilityDetails'])->name('instructor-availability.details');

Route::get('/instructor-availability/slots/{availabilityId}', [TeacherPreferedTeachingTimeController::class, 'getAvailabilitySlotsByTeacherAvailability'])->name('instructor-availability-slots');
