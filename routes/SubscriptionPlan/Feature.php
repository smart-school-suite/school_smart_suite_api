<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionPlan\FeatureController;

Route::post('/create', [FeatureController::class, 'createFeature'])->name('create.feature');
Route::put('/update', [FeatureController::class, 'updateFeature'])->name('update.feature');
Route::delete('/{featureId}/delete', [FeatureController::class, 'deleteFeature'])->name('delete.feature');
Route::get('/', [FeatureController::class, 'getFeatures'])->name('get.features');
Route::get('/{featureId}/activate', [FeatureController::class, 'activateFeature'])->name('activate.feature');
Route::get('/{featureId}/deactivate', [FeatureController::class, 'deactivateFeature'])->name('deactivate.feature');
