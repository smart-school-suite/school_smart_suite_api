<?php

namespace App\Http\Controllers;


use App\Http\Requests\EducationLevelRequest;
use App\Services\EducationLevelService;
use App\Http\Requests\UpdateEducationLevelRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class EducationLevelsController extends Controller
{
    protected EducationLevelService $educationLevelService;
    public function __construct(EducationLevelService $educationLevelService)
    {
        $this->educationLevelService = $educationLevelService;
    }
    public function createEducationLevel(EducationLevelRequest $request)
    {

        $educationLevel = $this->educationLevelService->createEducationLevel($request->validated());
        return ApiResponseService::success("Level Created Succefully", $educationLevel, null, 201);
    }

    public function updateEducationLevel(UpdateEducationLevelRequest $request, string $education_level_id)
    {

        $updateEducationLevel = $this->educationLevelService->updateEducationLevel($request->validated(), $education_level_id);
        return ApiResponseService::success("Education Level Update Sucessfully", $updateEducationLevel, null, 200);
    }

    public function deleteEducationLevel( string $education_level_id)
    {
        $deleteEducationLevel = $this->educationLevelService->deleteEducationLevel($education_level_id);
        return ApiResponseService::success("Education Level Deleted Sucessfully", $deleteEducationLevel, null, 200);
    }

    public function getEducationLevel()
    {
        $educationLevel = $this->educationLevelService->getEducationLevels();
        return ApiResponseService::success("Education Levels Fetched Succefully", $educationLevel, null, 200);
    }
}
