<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorAvailabilityController;

//review this routes and update the routes folder permissions

Route::middleware(['permission:teacher.avialability.create'])->post('/create', [InstructorAvailabilityController::class, 'createInstructorAvailability'])
    ->name('instructor-availability.create');

Route::middleware(['permission:teacher.avialability.create'])->post('/{targetAvailabilityId}/{availabilityId}/create',
  [InstructorAvailabilityController::class, 'createAvailabilityByOtherSlots']
)->name('instructor-availability.create');

Route::middleware(['permission:teacher.avialability.show'])->get('/teacher/{teacherId}/availability', [InstructorAvailabilityController::class, 'getInstructorAvailabilitesByTeacher'])
    ->name('teachers.availability.index');

Route::middleware(['permission:teacher.avialability.update'])->patch('/update', [InstructorAvailabilityController::class, 'bulkUpdateInstructorAvialabililty'])
       ->name('instructor-availability.bulk-update');

Route::get('/school-semesters/teacher/{teacherId}/specialty-preference', [InstructorAvailabilityController::class, 'getSchoolSemestersByTeacherSpecialtyPreference']);

Route::delete('/instructor-availability/teacher/{teacherId}/availability/{availabilityId}', [InstructorAvailabilityController::class, 'deleteAvailabilitySlots'])
    ->name('instructor-availability.delete-all-by-teacher-availability');

Route::get('/instructor-availabilities', [InstructorAvailabilityController::class, 'getInstructorAvailabilities'])->name('instructor-availability.get');

Route::get('/instructor-availability/{availabilityId}', [InstructorAvailabilityController::class, 'getInstructorAvailabilityDetails'])->name('instructor-availability.details');

Route::get('/instructor-availability/slots/{availabilityId}', [InstructorAvailabilityController::class, 'getAvailabilitySlotsByTeacherAvailability'])->name('instructor-availability-slots');
