<?php

use App\Http\Controllers\SchoolSetAudienceGroupController;
use Illuminate\Support\Facades\Route;

Route::post('/audience-groups/create', [SchoolSetAudienceGroupController::class, 'createAudienceGroup'])
    ->name('audience-groups.create');

Route::get('/audience-groups', [SchoolSetAudienceGroupController::class, 'getAudienceGroups'])
    ->name('audience-groups.index');

Route::put('/audience-groups/{schoolSetAudienceGroupId}', [SchoolSetAudienceGroupController::class, 'updateAudienceGroup'])
    ->name('audience-groups.update');

Route::delete('/audience-groups/{schoolSetAudienceGroupId}', [SchoolSetAudienceGroupController::class, 'deleteAudienceGroup'])
    ->name('audience-groups.delete');

Route::get('/audience-groups/{schoolSetAudienceGroupId}', [SchoolSetAudienceGroupController::class, 'getAudienceGroupDetails'])
    ->name('audience-groups.details');

Route::post('/audience-groups/add', [SchoolSetAudienceGroupController::class, 'addAudienceGroupMembers'])
    ->name('audience-groups.add-members');

Route::post('/audience-groups/remove', [SchoolSetAudienceGroupController::class, 'removeAudienceGroupMembers'])
    ->name('audience-groups.remove-members');

Route::get('/audience-groups/{schoolSetAudienceGroupId}', [SchoolSetAudienceGroupController::class, 'getAudienceGroupMembers'])
    ->name('audience-groups.members');
