<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradesCategoryController;

Route::post('/createGradeCategory', [GradesCategoryController::class, 'createCategory']);
Route::delete('/deleteGradeCategory/{categoryId}', [GradesCategoryController::class, 'deleteCategory']);
Route::put('/updateGradeCategory/{categoryId}', [GradesCategoryController::class, 'updateCategory']);
Route::get('/getGradeCategories', [GradesCategoryController::class, 'getGradesCategory']);
