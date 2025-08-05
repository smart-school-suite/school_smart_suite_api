<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryManagementController;
// Publicly accessible route
Route::get('/', [CountryManagementController::class, 'getCountries'])
    ->name('countries.index');

Route::middleware(['auth:sanctum'])->group(function () {
    // Create a new country
    Route::middleware(['permission:appAdmin.country.create'])->post('/', [CountryManagementController::class, 'createCountry'])
        ->name('countries.store');

    // Update a specific country
    Route::middleware(['permission:appAdmin.country.update'])->put('/{countryId}', [CountryManagementController::class, 'updateCountry'])
        ->name('countries.update');

    // Delete a specific country
    Route::middleware(['permission:appAdmin.country.delete'])->delete('/{countryId}', [CountryManagementController::class, 'deleteCountry'])
        ->name('countries.destroy');

    // Bulk delete countries
    Route::post('/bulk-delete', [CountryManagementController::class, 'bulkDeleteCountry'])
        ->name('countries.bulk-delete');

    // Bulk update countries
    Route::middleware(['permission:appAdmin.country.update'])->patch('/bulk-update', [CountryManagementController::class, 'bulkUpdateCountry'])
        ->name('countries.bulk-update');
});
