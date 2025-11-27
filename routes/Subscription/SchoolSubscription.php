<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscription\SchoolSubscriptionController;
// Subscribe a school to a plan
Route::post('/school-subscriptions', [SchoolSubscriptionController::class, 'subscribe'])
    ->name('school-subscriptions.store');
Route::middleware(['auth:sanctum'])->group(function () {
    // Get all subscribed schools
    Route::middleware(['permission:appAdmin.subscription.view.subscribed.schools'])->get('/school-subscriptions', [SchoolSubscriptionController::class, 'getSubscribedSchools'])
        ->name('school-subscriptions.index');

    // Get details of a specific school subscription
    Route::middleware(['permission:appAdmin.subscription.show.schoolBranch'])->get('/school-subscriptions/{subscriptionId}', [SchoolSubscriptionController::class, 'getSchoolSubscriptonDetails'])
        ->name('school-subscriptions.show');
});
