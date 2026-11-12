<?php

use App\Http\Controllers\Election\ElectionOfficeController;
use Illuminate\Support\Facades\Route;

// Office holders (elected, appointed and replacement) and tenure history
Route::get('/holders', [ElectionOfficeController::class, 'getOfficeHolders'])->name('election-office.holders.index');
Route::get('/holders/current', [ElectionOfficeController::class, 'getCurrentOfficeHolders'])->name('election-office.holders.current');
Route::post('/holders/appoint', [ElectionOfficeController::class, 'appointOfficeHolder'])->name('election-office.holders.appoint');
Route::get('/holders/{holderId}', [ElectionOfficeController::class, 'getOfficeHolderDetails'])->name('election-office.holders.show');
Route::post('/holders/{holderId}/replace', [ElectionOfficeController::class, 'replaceOfficeHolder'])->name('election-office.holders.replace');
Route::post('/holders/{holderId}/terminate', [ElectionOfficeController::class, 'terminateOfficeHolder'])->name('election-office.holders.terminate');
Route::get('/history/{participantType}/{participantId}', [ElectionOfficeController::class, 'getParticipantHistory'])
    ->whereIn('participantType', ['student', 'teacher', 'school_admin'])
    ->name('election-office.participant-history');
