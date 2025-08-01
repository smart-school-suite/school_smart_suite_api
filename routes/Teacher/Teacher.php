<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;

// Get all teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.view'])->get('/teachers', [TeacherController::class, 'getInstructors'])
    ->name('teachers.index');

// Get details of a specific teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.show'])->get('/{teacherId}', [TeacherController::class, 'getInstructorDetails'])
    ->name('teachers.show');

// Update a specific teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.update'])->put('/{teacherId}', [TeacherController::class, 'updateInstructor'])
    ->name('teachers.update');

// Delete a specific teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.delete'])->delete('/{teacherId}', [TeacherController::class, 'deleteInstructor'])
    ->name('teachers.destroy');

// Get timetable for a specific teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.view.time.timetable'])->get('/{teacherId}/timetable', [TeacherController::class, 'getTimettableByTeacher'])
    ->name('teachers.timetable.index');

// Assign a specialty preference to a teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.add.specialty.peference'])->post('/specialty-preference', [TeacherController::class, 'assignTeacherSpecailtyPreference'])
    ->name('teachers.specialty-preference.store');

// Deactivate a specific teacher/instructor account
Route::middleware(['permission:schoolAdmin.teacher.deactivate'])->post('/{teacherId}/deactivate', [TeacherController::class, 'deactivateTeacher'])
    ->name('teachers.deactivate');

// Activate a specific teacher/instructor account
Route::middleware(['permission:schoolAdmin.teacher.activate'])->post('/{teacherId}/activate', [TeacherController::class, 'activateTeacher'])
    ->name('teachers.activate');

// Bulk update teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.update'])->put('/bulk-update', [TeacherController::class, 'bulkUpdateTeacher'])
    ->name('teachers.bulk-update');

// Bulk activate teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.activate'])->post('/bulk-activate', [TeacherController::class, 'bulkActivateTeacher'])
    ->name('teachers.bulk-activate');

// Bulk deactivate teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.deactivate'])->post('/bulk-deactivate', [TeacherController::class, 'bulkDeactivateTeacher'])
    ->name('teachers.bulk-deactivate');

// Bulk delete teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.delete'])->delete('/bulk-delete', [TeacherController::class, 'bulkDeleteTeacher'])
    ->name('teachers.bulk-delete');

Route::post('/teacher/avatar/upload', [TeacherController::class, 'uploadProfilePicture'])
->name('teacher.avatar.upload');

Route::delete('/teacher/avatar/delete', [TeacherController::class, 'deleteProfilePicture'])
->name('teacher.avatar.delete');

Route::get('/teachers/specialty/{specialtyId}', [TeacherController::class, 'getTeacherBySpecialtyPreference'])
    ->name('teachers.specialty.preference');
