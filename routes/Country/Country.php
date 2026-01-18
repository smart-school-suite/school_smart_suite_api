<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Country\CountryController;
Route::get('/', [CountryController::class, 'getCountries'])
    ->name('countries.index');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [CountryController::class, 'createCountry'])
        ->name('countries.store');
    Route::put('/{countryId}', [CountryController::class, 'updateCountry'])
        ->name('countries.update');

    Route::delete('/{countryId}', [CountryController::class, 'deleteCountry'])
        ->name('countries.destroy');

    Route::post('/bulk-delete', [CountryController::class, 'bulkDeleteCountry'])
        ->name('countries.bulk-delete');

    Route::patch('/bulk-update', [CountryController::class, 'bulkUpdateCountry'])
        ->name('countries.bulk-update');
});
