<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryController;

// Publicly accessible route
Route::get('/countries', [CountryController::class, 'getCountries'])
    ->name('countries.index');

Route::middleware(['auth:sanctum'])->group(function () {
    // Create a new country
    Route::middleware(['permission:appAdmin.country.create'])->post('/countries', [CountryController::class, 'createCountry'])
        ->name('countries.store');

    // Update a specific country
    Route::middleware(['permission:appAdmin.country.update'])->put('/countries/{countryId}', [CountryController::class, 'updateCountry'])
        ->name('countries.update');

    // Delete a specific country
    Route::middleware(['permission:appAdmin.country.delete'])->delete('/countries/{countryId}', [CountryController::class, 'deleteCountry'])
        ->name('countries.destroy');

    // Bulk delete countries
    Route::post('/countries/bulk-delete', [CountryController::class, 'bulkDeleteCountry'])
        ->name('countries.bulk-delete');

    // Bulk update countries
    Route::middleware(['permission:appAdmin.country.update'])->patch('/countries/bulk-update', [CountryController::class, 'bulkUpdateCountry'])
        ->name('countries.bulk-update');
});
