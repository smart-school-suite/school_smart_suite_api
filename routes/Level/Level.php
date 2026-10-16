<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Level\LevelController;
use App\Http\Middleware\IdentifyTenant;

Route::post('/', [LevelController::class, 'createEducationLevel'])
    ->name('education-levels.store');

Route::middleware([IdentifyTenant::class])->get('/', [LevelController::class, 'getEducationLevelBySchoolBranch'])
    ->name('education-levels.index');

Route::put('/{levelId}', [LevelController::class, 'updateEducationLevel'])
    ->name('education-levels.update');

Route::delete('/{levelId}', [LevelController::class, 'deleteEducationLevel'])
    ->name('education-levels.destroy');

Route::get('/all', [LevelController::class, 'getEducationLevel'])
    ->name('get.all.levels');
