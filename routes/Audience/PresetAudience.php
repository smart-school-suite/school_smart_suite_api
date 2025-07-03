<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresetAudienceController;

Route::post('/create', [PresetAudienceController::class, 'createPresetAudience'])
    ->name('preset-audiences.store');

Route::put('/{audienceId}', [PresetAudienceController::class, 'updatePresetAudience'])
    ->name('preset-audiences.update');

Route::delete('/{audienceId}', [PresetAudienceController::class, 'deletePresetAudience'])
    ->name('preset-audiences.destroy');

Route::get('/active', [PresetAudienceController::class, 'getActivePresetAudiences'])
    ->name('preset-audiences.active');

Route::post('/activate/{audienceId}', [PresetAudienceController::class, 'activatePresetAudience'])
    ->name('preset-audiences.activate');

Route::get('/preset-audiences', [PresetAudienceController::class, 'getPresetAudiences'])
    ->name('preset-audiences.index');

Route::post('/deactivate/{audienceId}', [PresetAudienceController::class, 'deactivatePresetAudience'])
    ->name('preset-audiences.deactivate');
