<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\FinancialStatsController;

Route::middleware(['auth:sanctum'])->get("get/financial-stats", [FinancialStatsController::class, 'getFinanacialStats']);
