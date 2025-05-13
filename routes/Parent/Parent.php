<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParentsController;

// Get all parents
Route::get('/parents', [ParentsController::class, 'getAllParents'])
    ->name('parents.index');

// Get details of a specific parent
Route::get('/parents/{parentId}', [ParentsController::class, 'getParentDetails'])
    ->name('parents.show');

// Update a specific parent
Route::put('/parents/{parentId}', [ParentsController::class, 'updateParent'])
    ->name('parents.update');

// Delete a specific parent
Route::delete('/parents/{parentId}', [ParentsController::class, 'deleteParent'])
    ->name('parents.destroy');

// Bulk delete parents
Route::delete('/parents/bulk-delete/{parentIds}', [ParentsController::class, 'bulkDeleteParents'])
    ->name('parents.bulk-delete');

// Bulk update parents
Route::put('/parents/bulk-update', [ParentsController::class, 'BulkUpdateParents'])
    ->name('parents.bulk-update');
