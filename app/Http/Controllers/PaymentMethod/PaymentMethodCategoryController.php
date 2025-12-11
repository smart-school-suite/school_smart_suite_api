<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethod\CreatePaymentMethodCategoryRequest;
use App\Http\Requests\PaymentMethod\UpdatePaymentMethodCategoryRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\PaymentMethod\PaymentMethodCategoryService;
class PaymentMethodCategoryController extends Controller
{
    protected PaymentMethodCategoryService $paymentMethodCategoryService;

    public function __construct(PaymentMethodCategoryService $paymentMethodCategoryService)
    {
        $this->paymentMethodCategoryService = $paymentMethodCategoryService;
    }

    public function createCategory(CreatePaymentMethodCategoryRequest $request){
         $createCategory = $this->paymentMethodCategoryService->createCategory($request->validated());
         return ApiResponseService::success("Category Created Successfully", $createCategory, null, 201);
    }

    public function updateCategory(UpdatePaymentMethodCategoryRequest $request, $categoryId){
        $updateCategory = $this->paymentMethodCategoryService->updateCategory($request->validated(), $categoryId);
        return ApiResponseService::success("Category Updated Successfully", $updateCategory, null, 200);
    }

    public function getCategory(Request $request){
         $categories = $this->paymentMethodCategoryService->getCategory();
         return ApiResponseService::success("Category Fetched Successfully", $categories, null, 200);
    }

    public function deleteCategory(Request $request, $categoryId){
        $deleteCategory = $this->paymentMethodCategoryService->deleteCategory($categoryId);
        return ApiResponseService::success("Category Deleted Successfully", $deleteCategory, null, 200);
    }

    public function activateCategory(Request $request, $categoryId){
        $activateCategory = $this->paymentMethodCategoryService->activateCategory($categoryId);
        return ApiResponseService::success("Category Activated Successfully", $activateCategory, null, 200);
    }

    public function deactivateCategory(Request $request, $categoryId){
         $deactivateCategory = $this->paymentMethodCategoryService->deactivateCategory($categoryId);
         return ApiResponseService::success("Category Deactivated Successfully", $deactivateCategory, null, 200);
    }

    public function getCategoryDetails(Request $request, $categoryId){
         $categoryDetails = $this->paymentMethodCategoryService->getCategoryDetails($categoryId);
         return ApiResponseService::success("Category Details Fetched Successfully", $categoryDetails, null, 200);
    }

}
