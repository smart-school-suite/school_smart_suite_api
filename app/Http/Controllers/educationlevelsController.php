<?php

namespace App\Http\Controllers;


use App\Services\EducationLevelService;
use App\Http\Requests\Level\BulkUpdateLevelRequest;
use App\Http\Requests\Level\UpdateLevelRequest;
use App\Http\Requests\Level\CreateLevelRequest;
use App\Services\ApiResponseService;

class EducationLevelsController extends Controller
{
    protected EducationLevelService $educationLevelService;
    public function __construct(EducationLevelService $educationLevelService)
    {
        $this->educationLevelService = $educationLevelService;
    }
    public function createEducationLevel(CreateLevelRequest $request)
    {

        $educationLevel = $this->educationLevelService->createEducationLevel($request->validated());
        return ApiResponseService::success("Level Created Succefully", $educationLevel, null, 201);
    }

    public function updateEducationLevel(UpdateLevelRequest $request, string $levelId)
    {

        $updateEducationLevel = $this->educationLevelService->updateEducationLevel($request->validated(), $levelId);
        return ApiResponseService::success("Education Level Update Sucessfully", $updateEducationLevel, null, 200);
    }

    public function deleteEducationLevel( string $levelId)
    {
        $deleteEducationLevel = $this->educationLevelService->deleteEducationLevel($levelId);
        return ApiResponseService::success("Education Level Deleted Sucessfully", $deleteEducationLevel, null, 200);
    }

    public function getEducationLevel()
    {
        $educationLevel = $this->educationLevelService->getEducationLevels();
        return ApiResponseService::success("Education Levels Fetched Succefully", $educationLevel, null, 200);
    }
}
