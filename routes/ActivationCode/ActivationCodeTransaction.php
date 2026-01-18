<?php

use App\Http\Controllers\ActivationCode\ActivationCodeTransactionController;
use Illuminate\Support\Facades\Route;

Route::get("/", [ActivationCodeTransactionController::class, "getActivationCodeTransactions"])->name("activationCode.transactions");
