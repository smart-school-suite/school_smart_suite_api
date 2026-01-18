<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Parent\ParentController;

Route::get('/', [ParentController::class, 'getAllParents'])
    ->name('parents.index');

Route::post('/', [ParentController::class, 'createParent'])->name('parents.create');


Route::get('/{parentId}', [ParentController::class, 'getParentDetails'])
    ->name('parents.show');

Route::put('/{parentId}', [ParentController::class, 'updateParent'])
    ->name('parents.update');

Route::delete('/{parentId}', [ParentController::class, 'deleteParent'])
    ->name('parents.destroy');

Route::post('/bulk-delete', [ParentController::class, 'bulkDeleteParents'])
    ->name('parents.bulk-delete');

Route::patch('/bulk-update', [ParentController::class, 'BulkUpdateParents'])
    ->name('parents.bulk-update');
