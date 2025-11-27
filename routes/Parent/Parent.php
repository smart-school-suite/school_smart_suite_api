<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Parent\ParentController;

// Get all parents
Route::middleware(['permission:schoolAdmin.parent.view'])->get('/', [ParentController::class, 'getAllParents'])
    ->name('parents.index');

//create parent
Route::post('/', [ParentController::class, 'createParent'])->name('parents.create');
// Get details of a specific parent
Route::middleware(['permission:schoolAdmin.parent.show'])->get('/{parentId}', [ParentController::class, 'getParentDetails'])
    ->name('parents.show');

// Update a specific parent
Route::middleware(['permission:schoolAdmin.parent.update'])->put('/{parentId}', [ParentController::class, 'updateParent'])
    ->name('parents.update');

// Delete a specific parent
Route::middleware(['permission:schoolAdmin.parent.delete'])->delete('/{parentId}', [ParentController::class, 'deleteParent'])
    ->name('parents.destroy');

// Bulk delete parents
Route::middleware(['permission:schoolAdmin.parent.delete'])->post('/bulk-delete', [ParentController::class, 'bulkDeleteParents'])
    ->name('parents.bulk-delete');

// Bulk update parents
Route::middleware(['permission:schoolAdmin.parent.update'])->patch('/bulk-update', [ParentController::class, 'BulkUpdateParents'])
    ->name('parents.bulk-update');
