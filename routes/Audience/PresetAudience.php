<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresetAudienceController;

Route::post('/preset-audiences/create', [PresetAudienceController::class, 'createPresetAudience'])
    ->name('preset-audiences.store');

Route::put('/preset-audiences/{audienceId}', [PresetAudienceController::class, 'updatePresetAudience'])
    ->name('preset-audiences.update');

Route::delete('/preset-audiences/{audienceId}', [PresetAudienceController::class, 'deletePresetAudience'])
    ->name('preset-audiences.destroy');

Route::get('/preset-audiences/active', [PresetAudienceController::class, 'getActivePresetAudiences'])
    ->name('preset-audiences.active');

Route::post('/preset-audiences/activate/{audienceId}', [PresetAudienceController::class, 'activatePresetAudience'])
    ->name('preset-audiences.activate');

Route::get('/preset-audiences', [PresetAudienceController::class, 'getPresetAudiences'])
    ->name('preset-audiences.index');

Route::post('/preset-audiences/deactivate/{audienceId}', [PresetAudienceController::class, 'deactivatePresetAudience'])
    ->name('preset-audiences.deactivate');
