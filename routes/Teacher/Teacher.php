<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->delete('/delete-teacher/{teacher_id}', [TeacherController::class, 'deleteInstructor']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->put('/update-teacher/{teacher_id}', [TeacherController::class, 'updateInstructor']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/teacher-details/{teacher_id}', [TeacherController::class, 'getInstructorDetails']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/getallInstructors', [TeacherController::class, 'getInstructors']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-teacher-timetable/{teacher_id}', [TeacherController::class, 'getTimettableByTeacher']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/add-specailty-preference/{teacherId}', [TeacherController::class, 'assignTeacherSpecailtyPreference']);
Route::post("/deactivateAccount/{teacherId}", [TeacherController::class, 'deactivateTeacher']);
Route::post("/activateAccount/{teacherId}", [TeacherController::class, 'activateTeacher']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/bulkUpdateTeacher', [TeacherController::class, 'bulkUpdateTeacher']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/bulkActivateTeacher/{teacherIds}', [TeacherController::class, 'bulkActivateTeacher']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/bulkDeactivateTeacher/{teacherIds}', [TeacherController::class, 'bulkDeactivateTeacher']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/bulkDeleteTeacher/{teacherIds}', [TeacherController::class, 'bulkDeleteTeacher']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/bulkAddTeacherSpecialtyPreference/{teacherIds}', [TeacherController::class, 'bulkAddSpecialtyPreference']);
