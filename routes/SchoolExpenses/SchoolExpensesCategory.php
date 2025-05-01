<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpensesCategorycontroller;

Route::middleware(['auth:sanctum'])->post('/create-category', [ExpensesCategorycontroller::class, 'createCategory']);
Route::middleware(['auth:sanctum'])->delete('/delete-category/{category_expense_id}', [ExpensesCategorycontroller::class, 'deleteCategory']);
Route::middleware(['auth:sanctum'])->get('/get-category-expenses', [ExpensesCategorycontroller::class, 'getCategory']);
Route::middleware(['auth:sanctum'])->put('/update-category/{category_expense_id}', [ExpensesCategorycontroller::class, 'updateCategory']);
