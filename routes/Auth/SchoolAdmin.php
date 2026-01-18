<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\LimitSchoolAdmin;
use App\Http\Controllers\Auth\SchoolAdmin\ChangePasswordController;
use App\Http\Controllers\Auth\SchoolAdmin\LoginSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\LogoutSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\CreatesSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\GetAuthSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\ValidateOtpController;
use App\Http\Controllers\Auth\SchoolAdmin\PasswordResetController;
use App\Http\Controllers\SchoolAdmin\SchoolAdminController;


Route::post('/login', [LoginSchoolAdminController::class, 'loginShoolAdmin'])
    ->name('school-admin.login');

Route::post('/verify-otp', [ValidateOtpController::class, 'verifySchoolAdminOtp'])
    ->name('school-admin.otp.verify');

Route::post('/request-otp', [ValidateOtpController::class, 'requestNewCode'])
    ->name('school-admin.otp.resend');

Route::post('/password/reset', [PasswordResetController::class, 'resetSchoolAdminPassword'])
    ->name('school-admin.password.email');
Route::post('/password/reset/verify-otp', [PasswordResetController::class, 'verifySchoolAdminOtp'])
    ->name('school-admin.password.verify');
Route::post('/password/reset/update', [PasswordResetController::class, 'changeShoolAdminPasswordUnAuthenticated'])
    ->name('school-admin.password.update');

Route::post('/register/super-admin', [SchoolAdminController::class, 'createAdminOnSignup'])
    ->name('school-admin.register.super');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [LogoutSchoolAdminController::class, 'logoutSchoolAdmin'])
        ->name('school-admin.logout');

    Route::get('/me', [GetAuthSchoolAdminController::class, 'getAuthSchoolAdmin'])
        ->name('school-admin.me');

    Route::post('/password/change', [ChangePasswordController::class, 'changeSchoolAdminPassword'])
        ->name('school-admin.password.change');

    Route::post('/register', [CreatesSchoolAdminController::class, 'createSchoolAdmin'])
        ->middleware([LimitSchoolAdmin::class, IdentifyTenant::class,])
        ->name('school-admin.register');
});
