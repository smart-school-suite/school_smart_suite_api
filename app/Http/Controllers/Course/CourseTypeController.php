<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseType\CreateCourseTypeRequest;
use App\Http\Requests\CourseType\UpdateCourseTypeRequest;
use Illuminate\Http\Request;
use App\Services\Course\CourseTypeService;
use App\Services\ApiResponseService;
class CourseTypeController extends Controller
{
    protected CourseTypeService $courseTypeService;
    public function __construct(CourseTypeService $courseTypeService)
    {
        $this->courseTypeService = $courseTypeService;
    }

    public function createCourseType(CreateCourseTypeRequest $request)
    {
        $createCourseType = $this->courseTypeService->createCourseType($request->validated());
        return ApiResponseService::success("Hall Created Successfully", $createCourseType, null, 201);
    }

    public function updateCourseType(UpdateCourseTypeRequest $request, $CourseTypeId)
    {
        $updateCourseType = $this->courseTypeService->updateCourseType($request->validated(), $CourseTypeId);
        return ApiResponseService::success("Course Type Updated Successfully", $updateCourseType, null, 200);
    }

    public function getActiveCourseTypes(Request $request)
    {
        $activeCourseTypes = $this->courseTypeService->getActiveCourseTypes();
        return ApiResponseService::success("Active Course Types Fetched Successfully", $activeCourseTypes, null, 200);
    }

    public function getAllCourseTypes(Request $request)
    {
        $CourseTypes = $this->courseTypeService->getAllCourseTypes();
        return ApiResponseService::success("Course Types Fetched Successfully", $CourseTypes, null, 200);
    }

    public function deactivateCourseType(Request $request, $CourseTypeId)
    {
        $deactivateCourseType = $this->courseTypeService->deactivateCourseType($CourseTypeId);
        return ApiResponseService::success("Course Type Deactivated Successfully", $deactivateCourseType, null, 200);
    }

    public function activateCourseType(Request $request, $CourseTypeId)
    {
        $activateCourseType = $this->courseTypeService->activateCourseType($CourseTypeId);
        return ApiResponseService::success("Course Type Activated Successfully",  $activateCourseType, null, 200);
    }

    public function deleteCourseType(Request $request, $CourseTypeId)
    {
        $deleteCourseType = $this->courseTypeService->deleteCourseType($CourseTypeId);
        return ApiResponseService::success("Course Type Deleted Successfully", $deleteCourseType, null, 200);
    }
}
