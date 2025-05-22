<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParentsController;

// Get all parents
Route::middleware(['permission:schoolAdmin.parent.view'])->get('/parents', [ParentsController::class, 'getAllParents'])
    ->name('parents.index');

//create parent
Route::post('/parents', [ParentsController::class, 'createParent'])->name('parents.create');
// Get details of a specific parent
Route::middleware(['permission:schoolAdmin.parent.show'])->get('/parents/{parentId}', [ParentsController::class, 'getParentDetails'])
    ->name('parents.show');

// Update a specific parent
Route::middleware(['permission:schoolAdmin.parent.update'])->put('/parents/{parentId}', [ParentsController::class, 'updateParent'])
    ->name('parents.update');

// Delete a specific parent
Route::middleware(['permission:schoolAdmin.parent.delete'])->delete('/parents/{parentId}', [ParentsController::class, 'deleteParent'])
    ->name('parents.destroy');

// Bulk delete parents
Route::middleware(['permission:schoolAdmin.parent.delete'])->post('/parents/bulk-delete', [ParentsController::class, 'bulkDeleteParents'])
    ->name('parents.bulk-delete');

// Bulk update parents
Route::middleware(['permission:schoolAdmin.parent.update'])->patch('/parents/bulk-update', [ParentsController::class, 'BulkUpdateParents'])
    ->name('parents.bulk-update');
