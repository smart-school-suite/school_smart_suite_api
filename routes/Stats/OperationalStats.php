<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\OperationalStatController;

Route::get("/{year}", [OperationalStatController::class, "getSchoolOperationalStats"])->name("stat.operational");
