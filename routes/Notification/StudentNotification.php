<?php

use App\Http\Controllers\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('student.notification.getAll');
Route::post('/notification/read', [NotificationController::class, 'readAllNofications'])->name('student.notification.readAll');
//Route::post('/notification/register/device', [NotificationController::class, 'registerDevice'])->name('notification.registerDevice');
