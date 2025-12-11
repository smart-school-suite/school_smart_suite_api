<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethod\CreatePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\UpdatePaymentMethodRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\PaymentMethod\PaymentMethodService;
class PaymentMethodController extends Controller
{
    protected PaymentMethodService $paymentMethodService;
    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }

    public function createPaymentMethod(CreatePaymentMethodRequest $request){
        $createPaymentMethod = $this->paymentMethodService->createPaymentMethod($request->validated(), $request);
        return ApiResponseService::success("Payment Method Created Successfully", $createPaymentMethod, null, 201);
    }

    public function updatePaymentMethod(UpdatePaymentMethodRequest $request, $paymentMethodId){
         $updatePaymentMethod = $this->paymentMethodService->updatePaymentMethod($request->validated(),$paymentMethodId, $request);
         return ApiResponseService::success("Payment Method Updated Successfully", $updatePaymentMethod, null, 200);
    }

    public function getPaymentMethodCountryId(Request $request, $countryId){
         $paymentMethods = $this->paymentMethodService->getPaymentMethodCountryId($countryId);
         return ApiResponseService::success("Payment Methods Fetched Successfully", $paymentMethods, null, 200);
    }

    public function deactivatePaymentMethod(Request $request, $paymentMethodId){
        $deactivatedPaymentMethod = $this->paymentMethodService->deactivatePaymentMethod($paymentMethodId);
        return ApiResponseService::success("Deactivate Payment Method", $deactivatedPaymentMethod, null, 200);
    }

    public function activatePaymentMethod(Request $request, $paymentMethodId){
        $activatePaymentMethod = $this->paymentMethodService->activatePaymentMethod($paymentMethodId);
        return ApiResponseService::success("Payment Method Activated Successfully", $activatePaymentMethod, null, 200);
    }

    public function getPaymentMethodDetail(Request $request, $paymentMethodId){
         $paymentMethodDetail = $this->paymentMethodService->getPaymentMethodDetail($paymentMethodId);
         return ApiResponseService::success("Payment Method Details Fetched Successfully", $paymentMethodDetail, null, 200);
    }

    public function getAllPaymentMethod(Request $request){
         $paymentMethods = $this->paymentMethodService->getAllPaymentMethod();
         return ApiResponseService::success("Payment Methods Fetched Successfully", $paymentMethods, null, 200);
    }

    public function deletePaymentMethod(Request $request, $paymentMethodId){
         $deletePaymentMethod = $this->paymentMethodService->deletePaymentMethod($paymentMethodId);
         return ApiResponseService::success("Payment Method Deleted Successfully", $deletePaymentMethod, null, 200);
    }
}
