<?php

use App\Http\Controllers\CoursesController;
use Illuminate\Support\Facades\Route;

// Create a new course
Route::post('/courses', [CoursesController::class, 'createCourse'])
    ->name('courses.store');

// Get all courses (potentially admin-only)
Route::get('/courses', [CoursesController::class, 'getCourses'])
    ->name('courses.index');

// Get active courses
Route::get('/courses/active', [CoursesController::class, 'getActiveCourses'])
    ->name('courses.active');

// Get details of a specific course
Route::get('/courses/{courseId}', [CoursesController::class, 'getCourseDetails'])
    ->name('courses.show');

// Get courses for the authenticated user
Route::get('/my/courses', [CoursesController::class, 'getCourses'])
    ->name('my-courses.index');

// Get courses by specialty, level, and semester (consider renaming level to semester for consistency)
Route::get('/my/courses/specialties/{specialtyId}/semesters/{semesterId}', [CoursesController::class, 'getBySpecialtyLevelSemester'])
    ->name('my-courses.by-specialty-semester');

// Get courses by school semester and specialty
Route::get('/school-semesters/{semesterId}/specialties/{specialtyId}/courses', [CoursesController::class, 'getCoursesBySchoolSemester'])
    ->name('school-semesters.specialties.courses.index');

// Update a specific course
Route::put('/courses/{courseId}', [CoursesController::class, 'updateCourse'])
    ->name('courses.update');

// Activate a specific course
Route::post('/courses/{courseId}/activate', [CoursesController::class, 'activateCourse'])
    ->name('courses.activate');

// Deactivate a specific course
Route::post('/courses/{courseId}/deactivate', [CoursesController::class, 'deactivateCourse'])
    ->name('courses.deactivate');

// Delete a specific course
Route::delete('/courses/{courseId}', [CoursesController::class, 'deleteCourse'])
    ->name('courses.destroy');

// Bulk delete courses
Route::delete('/courses/bulk-delete/{courseIds}', [CoursesController::class, 'bulkDeleteCourse'])
    ->name('courses.bulk-delete');

// Bulk update courses
Route::put('/courses/bulk-update', [CoursesController::class, 'bulkUpdateCourse'])
    ->name('courses.bulk-update');

// Bulk activate courses
Route::post('/courses/bulk-activate/{courseIds}', [CoursesController::class, 'bulkActivateCourse'])
    ->name('courses.bulk-activate');

// Bulk deactivate courses
Route::post('/courses/bulk-deactivate/{courseIds}', [CoursesController::class, 'bulkDeactivateCourse'])
    ->name('courses.bulk-deactivate');
