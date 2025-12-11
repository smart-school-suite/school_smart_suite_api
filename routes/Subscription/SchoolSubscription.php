<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscription\SchoolSubscriptionController;
use App\Http\Middleware\IdentifyTenant;

Route::post('/subscribe', [SchoolSubscriptionController::class, 'subscribe'])
    ->name('school-subscriptions.store');

Route::middleware(['auth:sanctum', IdentifyTenant::class])->group(function () {
    Route::post('/upgrade', [SchoolSubscriptionController::class, 'upgradePlan'])->name('upgrade.subscription.plan');
    Route::post('/renew', [SchoolSubscriptionController::class, 'renewPlan'])->name('renew.subscription.plan');
    Route::get('/', [SchoolSubscriptionController::class, 'getSchoolSubscriptions'])->name('get.school.branch.subscriptions');
});
