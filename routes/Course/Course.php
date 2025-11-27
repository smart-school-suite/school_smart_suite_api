<?php


use App\Http\Controllers\Course\CourseController;
use Illuminate\Support\Facades\Route;

// Create a new course
Route::middleware(['permission:schoolAdmin.course.create'])->post('/courses', [CourseController::class, 'createCourse'])
    ->name('courses.store');

// Get all courses (potentially admin-only)
Route::middleware(['permission:schoolAdmin.course.view'])->get('/courses', [CourseController::class, 'getCourses'])
    ->name('courses.index');

// Get active courses
Route::middleware(['permission:schoolAdmin.course.view.active'])->get('/active', [CourseController::class, 'getActiveCourses'])
    ->name('courses.active');

// Get details of a specific course middleware(['permission:schoolAdmin.course.show'])->
Route::get('/{courseId}', [CourseController::class, 'getCourseDetails'])
    ->name('courses.show');


// Get courses by specialty, level, and semester (consider renaming level to semester for consistency)
Route::middleware(['permission:schoolAdmin.course.view'])->get('courses/specialty/{specialtyId}/semester/{semesterId}', [CourseController::class, 'getBySpecialtyLevelSemester'])
    ->name('my-courses.by-specialty-semester');

// Get courses by school semester and specialty
Route::middleware(['permission:schoolAdmin.course.view'])->get('/school-semesters/{semesterId}/specialties/{specialtyId}/courses', [CourseController::class, 'getCoursesBySchoolSemester'])
    ->name('school-semesters.specialties.courses.index');

// Update a specific course
Route::middleware(['permission:schoolAdmin.course.update'])->put('/{courseId}', [CourseController::class, 'updateCourse'])
    ->name('courses.update');

// Activate a specific course
Route::middleware(['permission:schoolAdmin.course.activate'])->post('/{courseId}/activate', [CourseController::class, 'activateCourse'])
    ->name('courses.activate');

// Deactivate a specific course
Route::middleware(['permission:schoolAdmin.course.deactivate'])->post('/{courseId}/deactivate', [CourseController::class, 'deactivateCourse'])
    ->name('courses.deactivate');

// Delete a specific course
Route::middleware(['permission:schoolAdmin.course.delete'])->delete('/{courseId}', [CourseController::class, 'deleteCourse'])
    ->name('courses.destroy');

// Bulk delete courses
Route::middleware(['permission:schoolAdmin.course.delete'])->post('/bulk-delete', [CourseController::class, 'bulkDeleteCourse'])
    ->name('courses.bulk-delete');

// Bulk update courses
Route::middleware(['permission:schoolAdmin.course.update'])->patch('/bulk-update', [CourseController::class, 'bulkUpdateCourse'])
    ->name('courses.bulk-update');

// Bulk activate courses
Route::middleware(['permission:schoolAdmin.course.activate'])->post('/bulk-activate', [CourseController::class, 'bulkActivateCourse'])
    ->name('courses.bulk-activate');

// Bulk deactivate courses
Route::middleware(['permission:schoolAdmin.course.deactivate'])->post('/bulk-deactivate', [CourseController::class, 'bulkDeactivateCourse'])
    ->name('courses.bulk-deactivate');

Route::get('/student/{studentId}', [CourseController::class, "getAllCoursesByStudentId"])->name("get.courses.by.studentId");
Route::get('/semester/{semesterId}/student/{studentId}', [CourseController::class, "getCoursesByStudentIdSemesterId"])->name("get.courses.by.semester.student");
Route::get('/specialty/{specialtyId}/semester/{semesterId}', [CourseController::class, 'getCoursesBySpecialtySemester'])
    ->name('courses.by-specialty-semester');
