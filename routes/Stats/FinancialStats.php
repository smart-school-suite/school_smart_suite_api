<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\FinancialStatController;

Route::get("/{year}", [FinancialStatController::class, 'getFinancialStats'])->name('stat.financial.stat');
