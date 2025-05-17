<?php

use App\Http\Controllers\CoursesController;
use Illuminate\Support\Facades\Route;

// Create a new course
Route::middleware(['permission:schoolAdmin.course.create'])->post('/courses', [CoursesController::class, 'createCourse'])
    ->name('courses.store');

// Get all courses (potentially admin-only)
Route::middleware(['permission:schoolAdmin.course.view'])->get('/courses', [CoursesController::class, 'getCourses'])
    ->name('courses.index');

// Get active courses
Route::middleware(['permission:schoolAdmin.course.view.active'])->get('/courses/active', [CoursesController::class, 'getActiveCourses'])
    ->name('courses.active');

// Get details of a specific course
Route::middleware(['permission:schoolAdmin.course.show'])->get('/courses/{courseId}', [CoursesController::class, 'getCourseDetails'])
    ->name('courses.show');


// Get courses by specialty, level, and semester (consider renaming level to semester for consistency)
Route::middleware(['permission:schoolAdmin.course.view'])->get('courses/specialty/{specialtyId}/semester/{semesterId}', [CoursesController::class, 'getBySpecialtyLevelSemester'])
    ->name('my-courses.by-specialty-semester');

// Get courses by school semester and specialty
Route::middleware(['permission:schoolAdmin.course.view'])->get('/school-semesters/{semesterId}/specialties/{specialtyId}/courses', [CoursesController::class, 'getCoursesBySchoolSemester'])
    ->name('school-semesters.specialties.courses.index');

// Update a specific course
Route::middleware(['permission:schoolAdmin.course.update'])->put('/courses/{courseId}', [CoursesController::class, 'updateCourse'])
    ->name('courses.update');

// Activate a specific course
Route::middleware(['permission:schoolAdmin.course.activate'])->post('/courses/{courseId}/activate', [CoursesController::class, 'activateCourse'])
    ->name('courses.activate');

// Deactivate a specific course
Route::middleware(['permission:schoolAdmin.course.deactivate'])->post('/courses/{courseId}/deactivate', [CoursesController::class, 'deactivateCourse'])
    ->name('courses.deactivate');

// Delete a specific course
Route::middleware(['permission:schoolAdmin.course.delete'])->delete('/courses/{courseId}', [CoursesController::class, 'deleteCourse'])
    ->name('courses.destroy');

// Bulk delete courses
Route::middleware(['permission:schoolAdmin.course.delete'])->post('/courses/bulk-delete', [CoursesController::class, 'bulkDeleteCourse'])
    ->name('courses.bulk-delete');

// Bulk update courses
Route::middleware(['permission:schoolAdmin.course.update'])->patch('/courses/bulk-update', [CoursesController::class, 'bulkUpdateCourse'])
    ->name('courses.bulk-update');

// Bulk activate courses
Route::middleware(['permission:schoolAdmin.course.activate'])->post('/courses/bulk-activate', [CoursesController::class, 'bulkActivateCourse'])
    ->name('courses.bulk-activate');

// Bulk deactivate courses
Route::middleware(['permission:schoolAdmin.course.deactivate'])->post('/courses/bulk-deactivate', [CoursesController::class, 'bulkDeactivateCourse'])
    ->name('courses.bulk-deactivate');
