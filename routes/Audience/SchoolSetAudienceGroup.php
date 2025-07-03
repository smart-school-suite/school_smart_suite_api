<?php

use App\Http\Controllers\SchoolSetAudienceGroupController;
use Illuminate\Support\Facades\Route;

Route::post('/create', [SchoolSetAudienceGroupController::class, 'createAudienceGroup'])
    ->name('audience-groups.create');

Route::get('/audience-groups', [SchoolSetAudienceGroupController::class, 'getAudienceGroups'])
    ->name('audience-groups.index');

Route::put('/{schoolSetAudienceGroupId}', [SchoolSetAudienceGroupController::class, 'updateAudienceGroup'])
    ->name('audience-groups.update');

Route::delete('/{schoolSetAudienceGroupId}', [SchoolSetAudienceGroupController::class, 'deleteAudienceGroup'])
    ->name('audience-groups.delete');

Route::get('/{schoolSetAudienceGroupId}', [SchoolSetAudienceGroupController::class, 'getAudienceGroupDetails'])
    ->name('audience-groups.details');

Route::post('/add', [SchoolSetAudienceGroupController::class, 'addAudienceGroupMembers'])
    ->name('audience-groups.add-members');

Route::post('/remove', [SchoolSetAudienceGroupController::class, 'removeAudienceGroupMembers'])
    ->name('audience-groups.remove-members');

Route::get('/{schoolSetAudienceGroupId}', [SchoolSetAudienceGroupController::class, 'getAudienceGroupMembers'])
    ->name('audience-groups.members');
