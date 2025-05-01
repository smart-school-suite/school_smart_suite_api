<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\ParentsController;

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/delete-parent/{parent_id}', [ParentsController::class, 'deleteParent']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/update-parent/{parent_id}', [ParentsController::class, 'updateParent']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/get-parents', [ParentsController::class, 'getAllParents']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/parent-details/{parent_id}', [ParentsController::class, 'getParentDetails']);
Route::delete('/bulkDeleteParent/{parentIds}', [ParentsController::class, 'bulkDeleteParents']);
Route::put('/bulkUpdateParent', [ParentsController::class, 'BulkUpdateParents']);
