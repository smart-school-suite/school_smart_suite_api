<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionPlan\PlanFeatureController;

Route::post('/assign', [PlanFeatureController::class, 'assignFeatureToPlan'])->name('assign.feature');
Route::post('/remove', [PlanFeatureController::class, 'removeAssignedFeatures'])->name('remove.assigned.features');
Route::get('/assignable/plan/{planId}', [PlanFeatureController::class, 'getAssignableFeatures'])->name('get.assignable.features');
Route::get('/assigned/plan/{planId}', [PlanFeatureController::class, 'getAssignedFeatures'])->name('get.assigned.features');

