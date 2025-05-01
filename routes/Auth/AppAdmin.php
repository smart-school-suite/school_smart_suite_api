<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AppAdmin\ChangeAppAdminPassword;
use App\Http\Controllers\Auth\AppAdmin\CreateAppAdminController;
use App\Http\Controllers\Auth\AppAdmin\LoginAppAdminController;
use App\Http\Controllers\Auth\AppAdmin\ValidateOtpController;
use App\Http\Controllers\Auth\AppAdmin\GetAuthAppAdminController;
use App\Http\Controllers\Auth\AppAdmin\LogoutAppAdminController;
use App\Http\Controllers\Auth\AppAdmin\PasswordResetController;


    Route::post('/create-admin', [CreateAppAdminController::class, 'createAppAdmin']);
    Route::post('/loginAppAdmin', [LoginAppAdminController::class, 'loginAppAdmin']);
    Route::middleware('auth:sanctum')->post('/logout-admin', [LogoutAppAdminController::class, 'logoutAppAdmin']);
    Route::middleware('auth:sanctum')->post('/change-password', [ChangeAppAdminPassword::class, 'cchangeAppAdminPassword']);
    Route::middleware('auth:sanctum')->get('/auth-edumanage-admin', [GetAuthAppAdminController::class, 'getAuthAppAdmin']);
    Route::post('/resetPassword', [PasswordResetController::class, 'resetAppAdminPassword']);
    Route::post('/validatePasswordResetOtp', [PasswordResetController::class, 'verifyAppAdminOtp']);
    Route::post('/updatePassword', [PasswordResetController::class, 'changeAppAdminPasswordUnAuthenticated']);
    Route::post('/validateLoginOtp', [ValidateOtpController::class, 'verifyAppAdminOtp']);
    Route::post('/requestNewOtp', [ValidateOtpController::class, 'requestNewotpCode']);
