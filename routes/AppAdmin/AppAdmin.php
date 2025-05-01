<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EdumanageAdminController;

Route::put('/update-admin/{edumanage_admin_id}', [EdumanageAdminController::class, 'updateAppAdmin']);
Route::get('/get-all-admins', [EdumanageAdminController::class, 'getAppAdmins']);
Route::delete('/delete-admin/{edumanage_admin_id}', [EdumanageAdminController::class, 'deleteAppAdmin']);
