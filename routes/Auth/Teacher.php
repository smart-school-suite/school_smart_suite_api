<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Teacher\ChangePasswordController;
use App\Http\Controllers\Auth\Teacher\LoginTeacherController;
use App\Http\Controllers\Auth\Teacher\logoutteachercontroller;
use App\Http\Controllers\Auth\Teacher\GetAuthTeacherController;
use App\Http\Controllers\Auth\Teacher\ResetPasswordController;
use App\Http\Controllers\Auth\Teacher\ValidateOtpController;
use App\Http\Controllers\Auth\Teacher\CreateteacherController;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\LimitTeachers;

Route::post('/login', [LoginTeacherController::class, 'loginInstructor']);
Route::middleware('auth:sanctum')->post('/change-password', [ChangePasswordController::class, 'changeInstructorPassword']);
Route::middleware('auth:sanctum')->post('/logout', [LogoutTeacherController::class, 'logoutInstructor']);
Route::middleware('auth:sanctum')->get('/auth-teacher', [GetAuthTeacherController::class, 'getAuthTeacher']);
Route::middleware([IdentifyTenant::class, LimitTeachers::class,  'auth:sanctum',])->post('/create-teacher', [CreateTeacherController::class, 'createInstructor']);
Route::post('/resetPassword', [ResetPasswordController::class, 'resetInstructorPassword']);
Route::post('/validatePasswordResetOtp', [ResetPasswordController::class, 'resetInstructorPassword']);
Route::post('/updatePassword', [ResetPasswordController::class, 'ChangeInstructorPasswordUnAuthenticated']);
Route::post('/validateLoginOtp', [ValidateOtpController::class, 'verifyInstructorLoginOtp']);
Route::post('/requestNewOtp', [ValidateOtpController::class, 'requestNewOtp']);
