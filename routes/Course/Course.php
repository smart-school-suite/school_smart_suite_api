<?php
use App\Http\Controllers\CoursesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->post('/create-course', [CoursesController::class, 'createCourse']);
Route::middleware(['auth:sanctum'])->delete('/delete-course/{course_id}', [CoursesController::class, 'deleteCourse']);
Route::middleware(['auth:sanctum'])->put('/update-course/{course_id}', [CoursesController::class, 'updateCourse']);
Route::middleware(['auth:sanctum'])->get('/my-courses', [CoursesController::class, 'getCourses']);
Route::middleware(['auth:sanctum'])->get('/course-details/{course_id}', [CoursesController::class, 'getCourseDetails']);
Route::middleware(['auth:sanctum'])->get('/my-courses/{specialty_id}/{semester_id}', [CoursesController::class, 'getBySpecialtyLevelSemester']);
Route::middleware(['auth:sanctum'])->post('/deactivateCourse/{courseId}', [CoursesController::class, 'deactivateCourse']);
Route::middleware(['auth:sanctum'])->post("/activateCourse/{courseId}", [CoursesController::class, 'activateCourse']);
Route::middleware(['auth:sanctum'])->get('/getCoursesBySchoolSemester/{semesterId}/{specialtyId}', [CoursesController::class, 'getCoursesBySchoolSemester']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteCourses/{courseIds}', [CoursesController::class, 'bulkDeleteCourse']);
Route::middleware(['auth:sanctum'])->get('/getActiveCourses', [CoursesController::class, 'getActiveCourses']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateCourse', [CoursesController::class, 'bulkUpdateCourse']);
Route::middleware(['auth:sanctum'])->post('/bulkActivateCourse/{courseIds}', [CoursesController::class, 'bulkActivateCourse']);
Route::middleware(['auth:sanctum'])->post('/bulkDeactivateCourse/{courseIds}', [CoursesController::class, 'bulkDeactivateCourse']);
