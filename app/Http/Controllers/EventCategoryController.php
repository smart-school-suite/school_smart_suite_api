<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventCategory\CreateEventCategoryRequest;
use App\Http\Requests\EventCategory\UpdateEventCategoryRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\EventCategoryService;
use Throwable;

class EventCategoryController extends Controller
{
    protected EventCategoryService $eventCategoryService;
    public function __construct(EventCategoryService $eventCategoryService){
        $this->eventCategoryService = $eventCategoryService;
    }

    public function createCategory(CreateEventCategoryRequest $request){
        try{
           $currentSchool = $request->attributes->get('currentSchool');
           $createCategory = $this->eventCategoryService->createCategory($request->validated(), $currentSchool);
           return $createCategory;
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function updateCategory(UpdateEventCategoryRequest $request, $categoryId){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $updateCategory = $this->eventCategoryService->updateCategory($request->validated(), $currentSchool, $categoryId);
            return ApiResponseService::success("Category Updated Successfully", $updateCategory, null, 200);
        }
        catch(Throwable $e){
           return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deleteCategory(Request $request, $categoryId){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $deleteCategory = $this->eventCategoryService->deleteCategory($categoryId, $currentSchool);
            return ApiResponseService::success("Category Deleted Successfully", $deleteCategory, null, 200);
        }
         catch(Throwable $e){
           return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function getCategoryByStatus(Request $request, $status){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $eventCategories = $this->eventCategoryService->getCategoryByStatus($currentSchool, $status);
            return ApiResponseService::success("Event Categories Fetched Successfully", $eventCategories, null, 200);
        }
         catch(Throwable $e){
           return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function getAllCategories(Request $request){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $getAllCategories = $this->eventCategoryService->getCategories($currentSchool);
            return ApiResponseService::success("Event Categories Fetched Successfully", $getAllCategories, null, 200);
        }
        catch(Throwable $e){
          return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deactivateCategory(Request $request, $categoryId){
        try{
            $deactivate = $this->eventCategoryService->deactivateCategory($categoryId);
            return ApiResponseService::success("Category Deactivated Successfully", $deactivate, null, 200);
        }
        catch(Throwable $e){
           return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function activateCategory(Request $request, $categoryId){
        try{
            $activate = $this->eventCategoryService->activateCategory($categoryId);
            return ApiResponseService::success("Category Activated Successfully", $activate, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
