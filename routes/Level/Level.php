<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Level\LevelController;

Route::post('/', [LevelController::class, 'createEducationLevel'])
    ->name('education-levels.store');

Route::get('/', [LevelController::class, 'getEducationLevel'])
    ->name('education-levels.index');

Route::put('/{levelId}', [LevelController::class, 'updateEducationLevel'])
    ->name('education-levels.update');

Route::delete('/{levelId}', [LevelController::class, 'deleteEducationLevel'])
    ->name('education-levels.destroy');
