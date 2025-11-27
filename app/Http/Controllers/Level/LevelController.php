<?php

namespace App\Http\Controllers\Level;

use App\Http\Controllers\Controller;
use App\Services\Level\LevelService;
use App\Http\Requests\Level\UpdateLevelRequest;
use App\Http\Requests\Level\CreateLevelRequest;
use App\Services\ApiResponseService;

class LevelController extends Controller
{
    protected LevelService $levelService;
    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }
    public function createEducationLevel(CreateLevelRequest $request)
    {

        $educationLevel = $this->levelService->createEducationLevel($request->validated());
        return ApiResponseService::success("Level Created Succefully", $educationLevel, null, 201);
    }

    public function updateEducationLevel(UpdateLevelRequest $request, string $levelId)
    {

        $updateEducationLevel = $this->levelService->updateEducationLevel($request->validated(), $levelId);
        return ApiResponseService::success("Education Level Update Sucessfully", $updateEducationLevel, null, 200);
    }

    public function deleteEducationLevel(string $levelId)
    {
        $deleteEducationLevel = $this->levelService->deleteEducationLevel($levelId);
        return ApiResponseService::success("Education Level Deleted Sucessfully", $deleteEducationLevel, null, 200);
    }

    public function getEducationLevel()
    {
        $educationLevel = $this->levelService->getEducationLevels();
        return ApiResponseService::success("Education Levels Fetched Succefully", $educationLevel, null, 200);
    }
}
