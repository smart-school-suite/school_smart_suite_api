<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EdumanageAdminController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Get all application administrators
    Route::get('/admins', [EdumanageAdminController::class, 'getAppAdmins'])
        ->name('admins.index');

    // Update a specific application administrator
    Route::put('/admins/{appAdminId}', [EdumanageAdminController::class, 'updateAppAdmin'])
        ->name('admins.update');

    // Delete a specific application administrator
    Route::delete('/admins/{appAdminId}', [EdumanageAdminController::class, 'deleteAppAdmin'])
        ->name('admins.destroy');
});
