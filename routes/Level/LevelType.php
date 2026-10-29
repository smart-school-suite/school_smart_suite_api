<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Level\LevelTypeController;

Route::get('/', [LevelTypeController::class, 'getLevelTypes'])->name('get.levelTypes');
