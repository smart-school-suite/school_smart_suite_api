<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParentsController;

// Get all parents
Route::middleware(['permission:schoolAdmin.parent.view'])->get('/', [ParentsController::class, 'getAllParents'])
    ->name('parents.index');

//create parent
Route::post('/', [ParentsController::class, 'createParent'])->name('parents.create');
// Get details of a specific parent
Route::middleware(['permission:schoolAdmin.parent.show'])->get('/{parentId}', [ParentsController::class, 'getParentDetails'])
    ->name('parents.show');

// Update a specific parent
Route::middleware(['permission:schoolAdmin.parent.update'])->put('/{parentId}', [ParentsController::class, 'updateParent'])
    ->name('parents.update');

// Delete a specific parent
Route::middleware(['permission:schoolAdmin.parent.delete'])->delete('/{parentId}', [ParentsController::class, 'deleteParent'])
    ->name('parents.destroy');

// Bulk delete parents
Route::middleware(['permission:schoolAdmin.parent.delete'])->post('/bulk-delete', [ParentsController::class, 'bulkDeleteParents'])
    ->name('parents.bulk-delete');

// Bulk update parents
Route::middleware(['permission:schoolAdmin.parent.update'])->patch('/bulk-update', [ParentsController::class, 'BulkUpdateParents'])
    ->name('parents.bulk-update');
