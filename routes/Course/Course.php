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
Route::middleware(['permission:schoolAdmin.course.view.active'])->get('/active', [CoursesController::class, 'getActiveCourses'])
    ->name('courses.active');

// Get details of a specific course middleware(['permission:schoolAdmin.course.show'])->
Route::get('/{courseId}', [CoursesController::class, 'getCourseDetails'])
    ->name('courses.show');


// Get courses by specialty, level, and semester (consider renaming level to semester for consistency)
Route::middleware(['permission:schoolAdmin.course.view'])->get('courses/specialty/{specialtyId}/semester/{semesterId}', [CoursesController::class, 'getBySpecialtyLevelSemester'])
    ->name('my-courses.by-specialty-semester');

// Get courses by school semester and specialty
Route::middleware(['permission:schoolAdmin.course.view'])->get('/school-semesters/{semesterId}/specialties/{specialtyId}/courses', [CoursesController::class, 'getCoursesBySchoolSemester'])
    ->name('school-semesters.specialties.courses.index');

// Update a specific course
Route::middleware(['permission:schoolAdmin.course.update'])->put('/{courseId}', [CoursesController::class, 'updateCourse'])
    ->name('courses.update');

// Activate a specific course
Route::middleware(['permission:schoolAdmin.course.activate'])->post('/{courseId}/activate', [CoursesController::class, 'activateCourse'])
    ->name('courses.activate');

// Deactivate a specific course
Route::middleware(['permission:schoolAdmin.course.deactivate'])->post('/{courseId}/deactivate', [CoursesController::class, 'deactivateCourse'])
    ->name('courses.deactivate');

// Delete a specific course
Route::middleware(['permission:schoolAdmin.course.delete'])->delete('/{courseId}', [CoursesController::class, 'deleteCourse'])
    ->name('courses.destroy');

// Bulk delete courses
Route::middleware(['permission:schoolAdmin.course.delete'])->post('/bulk-delete', [CoursesController::class, 'bulkDeleteCourse'])
    ->name('courses.bulk-delete');

// Bulk update courses
Route::middleware(['permission:schoolAdmin.course.update'])->patch('/bulk-update', [CoursesController::class, 'bulkUpdateCourse'])
    ->name('courses.bulk-update');

// Bulk activate courses
Route::middleware(['permission:schoolAdmin.course.activate'])->post('/bulk-activate', [CoursesController::class, 'bulkActivateCourse'])
    ->name('courses.bulk-activate');

// Bulk deactivate courses
Route::middleware(['permission:schoolAdmin.course.deactivate'])->post('/bulk-deactivate', [CoursesController::class, 'bulkDeactivateCourse'])
    ->name('courses.bulk-deactivate');

Route::get('/student/{studentId}', [CoursesController::class, "getAllCoursesByStudentId"])->name("get.courses.by.studentId");
Route::get('/semester/{semesterId}/student/{studentId}', [CoursesController::class, "getCoursesByStudentIdSemesterId"])->name("get.courses.by.semester.student");
Route::get('/specialty/{specialtyId}/semester/{semesterId}', [CoursesController::class, 'getCoursesBySpecialtySemester'])
    ->name('courses.by-specialty-semester');
