<?php

namespace App\Http\Controllers\Announcement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AnnouncementCategory\CreateAnnouncementCategoryRequest;
use App\Http\Requests\AnnouncementCategory\UpdateAnnouncementCategoryRequest;
use App\Services\Announcement\AnnouncementCategoryService;
use App\Services\ApiResponseService;
use Throwable;

class AnnouncementCategoryController extends Controller
{
    protected AnnouncementCategoryService $announcementCategoryService;
    public function __construct(AnnouncementCategoryService $announcementCategoryService)
    {
        $this->announcementCategoryService = $announcementCategoryService;
    }

    public function createCategory(CreateAnnouncementCategoryRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $createCategory = $this->announcementCategoryService->createCategory($request->validated(), $currentSchool);
            return ApiResponseService::success("Announcement Category Created Successfully", $createCategory, null, 201);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function updateCategory(UpdateAnnouncementCategoryRequest $request, $categoryId)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $updateCategory = $this->announcementCategoryService->updateCategory($request->validated(), $currentSchool, $categoryId);
            return ApiResponseService::success("Announcement Category Updated Successfully", $updateCategory, null, 200);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function getCategories(Request $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $getCategories = $this->announcementCategoryService->getCategories($currentSchool);
            return ApiResponseService::success("Announcement Category Fetched Successfully", $getCategories, null, 200);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deleteCategory(Request $request, $categoryId)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $deleteCategory = $this->announcementCategoryService->deleteCategory($currentSchool, $categoryId);
            return ApiResponseService::success("Annoucement Category Deleted Successfully", $deleteCategory, null, 200);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
