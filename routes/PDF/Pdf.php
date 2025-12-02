<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDF\PDFGenerationController;


Route::post('/generate', [PDFGenerationController::class, 'generatePdf'])
    ->name('pdf.generate');
