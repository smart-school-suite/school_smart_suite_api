<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Student\LoginStudentController;
use App\Http\Controllers\Auth\Student\LogoutStudentController;
use App\Http\Controllers\Auth\Student\CreateStudentController;
use App\Http\Controllers\Auth\Student\GetAuthStudentController;
use App\Http\Controllers\Auth\Student\ChangePasswordController;
use App\Http\Controllers\Auth\Student\ResetPasswordController;
use App\Http\Controllers\Auth\Student\ValidateOtpController;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\Limitstudents;

// Login
Route::post('/login', [LoginStudentController::class, 'loginStudent'])
    ->name('student.login');

// OTP Verification (for login)
Route::post('/verify-otp', [ValidateOtpController::class, 'verifyOtp'])
    ->name('student.otp.verify');

// Request New OTP (for login)
Route::post('/request-otp', [ValidateOtpController::class, 'requestNewOtp'])
    ->name('student.otp.resend');

// Password Reset
Route::post('/password/reset', [ResetPasswordController::class, 'resetStudentPassword'])
    ->name('student.password.email');
Route::post('/password/reset/verify-otp', [ResetPasswordController::class, 'verifyStudentOtp'])
    ->name('student.password.verify');
Route::post('/password/reset/update', [ResetPasswordController::class, 'changeStudentPasswordUnAuthenticated'])
    ->name('student.password.update');

// Authenticated routes (requires sanctum and tenant identification)
Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    // Logout
    Route::post('/logout', [LogoutStudentController::class, 'logoutStudent'])
        ->name('student.logout');

    // Get authenticated student details
        Route::get('/me', [GetAuthStudentController::class, 'getAuthStudent'])
        ->name('student.me');

    // Change Password (authenticated)
    Route::post('/password/change', [ChangePasswordController::class, 'changeStudentPassword'])
        ->name('student.password.change');

    // Create new student (requires tenant identification and student limit)
    Route::post('/register', [CreateStudentController::class, 'createStudent'])
        ->middleware(Limitstudents::class)
        ->name('student.register');
});
