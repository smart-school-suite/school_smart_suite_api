<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionPlan\PlanController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/create', [PlanController::class, 'createPlan'])->name('create.plan');
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/update/{planId}', [PlanController::class, 'updatePlan'])->name('update.plan');
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/delete/{planId}', [PlanController::class, 'deletePlan'])->name('delete.plan');
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/', [PlanController::class, 'getAllPlans'])->name('get.all.plans');
Route::get('/country/{countryId}', [PlanController::class, 'getPlanCountryId'])->name('get.plan.countryId');
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/activate/{planId}', [PlanController::class, 'activatePlan'])->name('activate.plan');
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/deactivate/{planId}', [PlanController::class, 'deactivatePlan'])->name('deactivate.plan');
