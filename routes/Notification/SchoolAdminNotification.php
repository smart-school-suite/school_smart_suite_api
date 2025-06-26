<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

   Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notification.getAll');
   Route::post('/notificaiton/read', [NotificationController::class, 'readAllNofications'])->name('notification.readAll');
   Route::post('/notification/register/device/{actorId}', [NotificationController::class, 'registerDevice'])->name('notification.registerDevice');
   Route::post('/test-notification/{actorId}', [NotificationController::class, 'testNotification']);

