<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryController;

Route::middleware(['auth:sanctum'])->post('/create-country', [CountryController::class, 'createCountry']);
Route::get('/countries', [CountryController::class, 'getCountries']);
Route::middleware(['auth:sanctum'])->delete('/delete-country/{country_id}', [CountryController::class, 'deleteCountry']);
Route::middleware(['auth:sanctum'])->put('/update-country/{country_id}', [CountryController::class, 'updateCountry']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteCountry/{countryIds}', [CountryController::class, 'bulkDeleteCountry']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateCountry', [CountryController::class, 'bulkUpdateCountry']);
