<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\FinancialStatController;

Route::post('resit-fee/paid-vs-debt/by-level-exam-type/{year}', [FinancialStatController::class, 'getResitFeePaidVsDebtLevelExamType'])
        ->name('resit-fee.paid-vs-debt.by-level-exam-type');

    Route::get('resit-fee/payment-rate/{year}', [FinancialStatController::class, 'getResitFeePaymentRate'])
        ->name('resit-fee.payment-rate');

    Route::get('cards/{year}', [FinancialStatController::class, 'getCardStats'])
        ->name('cards');

    Route::get('additional-fee/paid-vs-unpaid/by-category/{year}', [FinancialStatController::class, 'getAdditionalFeePaidVsUnpaidCategory'])
        ->name('additional-fee.paid-vs-unpaid.by-category');

    Route::get('additional-fee/paid-vs-unpaid/by-level/{year}', [FinancialStatController::class, 'getAdditionalFeePaidVsUnpaidLevel'])
        ->name('additional-fee.paid-vs-unpaid.by-level');

    Route::get('additional-fee/payment-rate/{year}', [FinancialStatController::class, 'getAdditionalFeePaymentRate'])
        ->name('additional-fee.payment-rate');

    Route::get('revenue/total/{year}', [FinancialStatController::class, 'getSchoolRevenue'])
        ->name('revenue.total');

    Route::get('revenue/by-source/{year}', [FinancialStatController::class, 'getSchoolRevenueSource'])
        ->name('revenue.by-source');

    Route::get('registration-fee/paid-vs-debt/by-level/{year}', [FinancialStatController::class, 'getRegistrationFeePaidVsDebtLevel'])
        ->name('registration-fee.paid-vs-debt.by-level');

    Route::get('registration-fee/payment-rate/{year}', [FinancialStatController::class, 'getRegistrationFeePaidPaymentRate'])
        ->name('registration-fee.payment-rate');

    Route::get('tuition-fee/payment-rate/{year}', [FinancialStatController::class, 'getTuitionFeePaymentRate'])
        ->name('tuition-fee.payment-rate');

    Route::get('tuition-fee/paid-vs-unpaid/by-level/{year}', [FinancialStatController::class, 'getTuitionFeePaidVsUnpaidLevel'])
        ->name('tuition-fee.paid-vs-unpaid.by-level');

    Route::get('school-expense/monthly/{year}', [FinancialStatController::class, "getMonthlySchoolExpense"])->name("monthly.school_expense");
    Route::get('school-expense/category/{year}', [FinancialStatController::class, "getSchoolExpenseCategory"])->name("school_expense.category");
