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

// Login
Route::post('/login', [LoginTeacherController::class, 'loginInstructor'])
    ->name('teacher.login');

// OTP Verification (for login)
Route::post('/verify-otp', [ValidateOtpController::class, 'verifyInstructorLoginOtp'])
    ->name('teacher.otp.verify');

// Request New OTP (for login)
Route::post('/request-otp', [ValidateOtpController::class, 'requestNewOtp'])
    ->name('teacher.otp.resend');

// Password Reset
Route::post('/password/reset', [ResetPasswordController::class, 'resetInstructorPassword'])
    ->name('teacher.password.email');
Route::post('/password/reset/verify-otp', [ResetPasswordController::class, 'resetInstructorPassword'])
    ->name('teacher.password.verify');
Route::post('/password/reset/update', [ResetPasswordController::class, 'ChangeInstructorPasswordUnAuthenticated'])
    ->name('teacher.password.update');

// Authenticated routes (requires sanctum and tenant identification)
Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    // Logout
    Route::post('/logout', [LogoutTeacherController::class, 'logoutInstructor'])
        ->name('teacher.logout');

    // Get authenticated teacher details
    Route::get('/me', [GetAuthTeacherController::class, 'getAuthTeacher'])
        ->name('teacher.me');

    // Change Password (authenticated)
    Route::post('/password/change', [ChangePasswordController::class, 'changeInstructorPassword'])
        ->name('teacher.password.change');

    // Create new teacher (requires tenant identification and teacher limit)
    Route::post('/register', [CreateteacherController::class, 'createInstructor'])
        ->middleware(LimitTeachers::class, 'permission:schoolAdmin.teacher.create')
        ->name('teacher.register');
});
