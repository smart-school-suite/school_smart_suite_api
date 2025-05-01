<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolSubscriptionController;

Route::post('/subscribe', [SchoolSubscriptionController::class, 'subscribe']);
Route::get('/subscribed-schools', [SchoolSubscriptionController::class, 'getSubscribedSchools']);
Route::get('/subscription-details/{subscription_id}', [SchoolSubscriptionController::class, 'getSchoolSubscriptonDetails']);
