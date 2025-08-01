<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryController;

// Publicly accessible route
Route::get('/', [CountryController::class, 'getCountries'])
    ->name('countries.index');

Route::middleware(['auth:sanctum'])->group(function () {
    // Create a new country
    Route::middleware(['permission:appAdmin.country.create'])->post('/', [CountryController::class, 'createCountry'])
        ->name('countries.store');

    // Update a specific country
    Route::middleware(['permission:appAdmin.country.update'])->put('/{countryId}', [CountryController::class, 'updateCountry'])
        ->name('countries.update');

    // Delete a specific country
    Route::middleware(['permission:appAdmin.country.delete'])->delete('/{countryId}', [CountryController::class, 'deleteCountry'])
        ->name('countries.destroy');

    // Bulk delete countries
    Route::post('/bulk-delete', [CountryController::class, 'bulkDeleteCountry'])
        ->name('countries.bulk-delete');

    // Bulk update countries
    Route::middleware(['permission:appAdmin.country.update'])->patch('/bulk-update', [CountryController::class, 'bulkUpdateCountry'])
        ->name('countries.bulk-update');
});
