<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;

// Get all teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.view'])->get('/teachers', [TeacherController::class, 'getInstructors'])
    ->name('teachers.index');

// Get details of a specific teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.show'])->get('/teachers/{teacherId}', [TeacherController::class, 'getInstructorDetails'])
    ->name('teachers.show');

// Update a specific teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.update'])->put('/teachers/{teacherId}', [TeacherController::class, 'updateInstructor'])
    ->name('teachers.update');

// Delete a specific teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.delete'])->delete('/teachers/{teacherId}', [TeacherController::class, 'deleteInstructor'])
    ->name('teachers.destroy');

// Get timetable for a specific teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.view.time.timetable'])->get('/teachers/{teacherId}/timetable', [TeacherController::class, 'getTimettableByTeacher'])
    ->name('teachers.timetable.index');

// Assign a specialty preference to a teacher/instructor
Route::middleware(['permission:schoolAdmin.teacher.add.specialty.peference'])->post('/teachers/{teacherId}/specialty-preference', [TeacherController::class, 'assignTeacherSpecailtyPreference'])
    ->name('teachers.specialty-preference.store');

// Deactivate a specific teacher/instructor account
Route::middleware(['permission:schoolAdmin.teacher.deactivate'])->post('/teachers/{teacherId}/deactivate', [TeacherController::class, 'deactivateTeacher'])
    ->name('teachers.deactivate');

// Activate a specific teacher/instructor account
Route::middleware(['permission:schoolAdmin.teacher.activate'])->post('/teachers/{teacherId}/activate', [TeacherController::class, 'activateTeacher'])
    ->name('teachers.activate');

// Bulk update teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.update'])->put('/teachers/bulk-update', [TeacherController::class, 'bulkUpdateTeacher'])
    ->name('teachers.bulk-update');

// Bulk activate teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.activate'])->post('/teachers/bulk-activate/{teacherIds}', [TeacherController::class, 'bulkActivateTeacher'])
    ->name('teachers.bulk-activate');

// Bulk deactivate teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.deactivate'])->post('/teachers/bulk-deactivate/{teacherIds}', [TeacherController::class, 'bulkDeactivateTeacher'])
    ->name('teachers.bulk-deactivate');

// Bulk delete teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.delete'])->delete('/teachers/bulk-delete/{teacherIds}', [TeacherController::class, 'bulkDeleteTeacher'])
    ->name('teachers.bulk-delete');

// Bulk add specialty preferences to teachers/instructors
Route::middleware(['permission:schoolAdmin.teacher.add.specialty.peference'])->post('/teachers/bulk-add-specialty-preferences', [TeacherController::class, 'bulkAddSpecialtyPreference'])
    ->name('teachers.bulk-add-specialty-preferences');
