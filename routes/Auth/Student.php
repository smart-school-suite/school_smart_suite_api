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

Route::post('/login', [LoginStudentController::class, 'loginStudent']);
Route::middleware('auth:sanctum')->post('/logout', [LogoutStudentController::class, 'logoutStudent']);
Route::middleware('auth:sanctum')->post('/change-password', [ChangePasswordController::class, 'changeStudentPassword']);
Route::middleware('auth:sanctum')->post('/auth-student', [GetAuthStudentController::class, 'getAuthStudent']);
Route::middleware([IdentifyTenant::class, Limitstudents::class, 'auth:sanctum'])->post('/create-student', [CreateStudentController::class, 'createStudent']);
Route::post('/resetPassword', [ResetPasswordController::class, 'resetStudentPassword']);
Route::post('/validatePasswordResetOtp', [ResetPasswordController::class, 'verifyStudentOtp']);
Route::post('/updatePassword', [ResetPasswordController::class, 'changeStudentPasswordUnAuthenticated']);
Route::post('/validateLoginOtp', [ValidateOtpController::class, 'verifyInstructorLoginOtp']);
Route::post('/requestNewOtp', [ValidateOtpController::class, 'requestNewOtp']);
