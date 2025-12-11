<?php
 use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivationCode\ActivationCodeTypeController;
use App\Http\Middleware\IdentifyTenant;

Route::post('/create', [ActivationCodeTypeController::class, "createActivationCodeType"])->name("create.activationCodeType");
Route::put('/update/{activationCodeTypeId}', [ActivationCodeTypeController::class, "updateActivationCodeType"])->name("update.activationCodeType");
Route::get('/', [ActivationCodeTypeController::class, "getActivationCodeType"])->name("get.activationCodeType");
Route::get('/{activationCodeTypeId}', [ActivationCodeTypeController::class, "getActivationCodeTypeDetail"])->name("get.activationCodeType.details");
Route::delete('/delete/{activationCodeTypeId}', [ActivationCodeTypeController::class, "deleteActivationCodeType"])->name("delete.activationCodeType");
Route::post('/activate/{activationCodeTypeId}', [ActivationCodeTypeController::class, "activationActivationCodeType"])->name("activate.activationCodeType");
Route::post('/deactivate/{activationCodeTypeId}', [ActivationCodeTypeController::class, "deactivateActivationCodeType"])->name("deactivate.activationCodeType");
Route::middleware([IdentifyTenant::class])->get('/school', [ActivationCodeTypeController::class, "getActivationCodeTypeCountryId"])->name("get.activationCodeTypeSchoolBranch");
