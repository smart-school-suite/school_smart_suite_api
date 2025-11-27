<?php

use App\Http\Controllers\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

   Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notification.getAll');
   Route::post('/notificaiton/read', [NotificationController::class, 'readAllNofications'])->name('notification.readAll');
   Route::post('/notification/register/device', [NotificationController::class, 'registerDevice'])->name('notification.registerDevice');
   Route::post('/test-notification/{actorId}', [NotificationController::class, 'testNotification']);

