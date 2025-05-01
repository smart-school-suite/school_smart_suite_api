<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('api/v1/notification')->group( function(){
   Route::get('/getMyNotifications', [NotificationController::class, 'getNotifications']);
   Route::post('/readAllNotifications', [NotificationController::class, 'readAllNofications']);
   Route::post('/registerDevice/{actorId}', [NotificationController::class, 'registerDevice']);
   Route::post('/test-notification/{actorId}', [NotificationController::class, 'testNotification']);
});
