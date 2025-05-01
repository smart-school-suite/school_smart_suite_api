<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentAdditionalFeesController;

Route::post("/createFee", [StudentAdditionalFeesController::class, 'createStudentAdditionalFees']);
Route::put('/updateFee/{feeId}', [StudentAdditionalFeesController::class, 'updateStudentAdditionalFees']);
Route::delete('/deleteFee/{feeId}', [StudentAdditionalFeesController::class, 'deleteStudentAdditionalFees']);
Route::get('/getbyStudent/{studentId}', [StudentAdditionalFeesController::class, 'getStudentAdditionalFees']);
Route::get('/getAll', [StudentAdditionalFeesController::class, 'getAdditionalFees']);
Route::post('/payFee', [StudentAdditionalFeesController::class, 'payAdditionalFees']);
Route::get('/getTransactions', [StudentAdditionalFeesController::class, 'getAdditionalFeesTransactions']);
Route::delete('/deleteTransaction/{transactionId}', [StudentAdditionalFeesController::class, 'deleteTransaction']);
Route::delete('/reverseTransaction/{transactionId}', [StudentAdditionalFeesController::class, 'reverseAdditionalFeesTransaction']);
Route::get("/getTransactionDetails/{transactionId}", [StudentAdditionalFeesController::class, 'getTransactionDetails']);
Route::delete("/bulkDeleteAdditionalFees/{additionalFeeIds}", [StudentAdditionalFeesController::class, 'bulkDeleteStudentAdditionalFees']);
Route::delete("/bulkDeleteTransaction/{transactionIds}", [StudentAdditionalFeesController::class, 'bulkDeleteTransaction']);
Route::post("/bulkBillStudent", [StudentAdditionalFeesController::class, 'bulkBillStudents']);
Route::post("/bulkReverseTransaction/{transactionIds}", [StudentAdditionalFeesController::class, 'bulkReverseTransaction']);
Route::post('/bulkPayFee', [StudentAdditionalFeesController::class, 'bulkPayFees']);
