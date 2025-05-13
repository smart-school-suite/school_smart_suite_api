<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AppAdmin\ChangeAppAdminPassword;
use App\Http\Controllers\Auth\AppAdmin\CreateAppAdminController;
use App\Http\Controllers\Auth\AppAdmin\LoginAppAdminController;
use App\Http\Controllers\Auth\AppAdmin\ValidateOtpController;
use App\Http\Controllers\Auth\AppAdmin\GetAuthAppAdminController;
use App\Http\Controllers\Auth\AppAdmin\LogoutAppAdminController;
use App\Http\Controllers\Auth\AppAdmin\PasswordResetController;


// Registration
Route::post('/register', [CreateAppAdminController::class, 'createAppAdmin'])
    ->name('admin.register');

// Login
Route::post('/login', [LoginAppAdminController::class, 'loginAppAdmin'])
    ->name('admin.login');

// Password Reset
Route::post('/password/reset', [PasswordResetController::class, 'resetAppAdminPassword'])
    ->name('admin.password.email');
Route::post('/password/reset/verify-otp', [PasswordResetController::class, 'verifyAppAdminOtp'])
    ->name('admin.password.verify');
Route::post('/password/reset/update', [PasswordResetController::class, 'changeAppAdminPasswordUnAuthenticated'])
    ->name('admin.password.update');

// OTP Validation (for login)
Route::post('/otp/verify', [ValidateOtpController::class, 'verifyAppAdminOtp'])
    ->name('admin.otp.verify');
Route::post('/otp/resend', [ValidateOtpController::class, 'requestNewotpCode'])
    ->name('admin.otp.resend');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [LogoutAppAdminController::class, 'logoutAppAdmin'])
        ->name('admin.logout');

    // Change Password (authenticated)
    Route::post('/password/change', [ChangeAppAdminPassword::class, 'cchangeAppAdminPassword'])
        ->name('admin.password.change');

    // Get authenticated admin details
    Route::get('/me', [GetAuthAppAdminController::class, 'getAuthAppAdmin'])
        ->name('admin.me');
});
