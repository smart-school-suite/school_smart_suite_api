<?php

namespace App\Http\Controllers;

use App\Models\Educationlevels;
use App\Http\Requests\EducationLevelRequest;
use App\Services\EducationLevelService;
use App\Http\Requests\UpdateEducationLevelRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class educationlevelsController extends Controller
{
    protected EducationLevelService $educationLevelService;
    public function __construct(EducationLevelService $educationLevelService)
    {
        $this->educationLevelService = $educationLevelService;
    }
    public function create_education_levels(EducationLevelRequest $request)
    {

        $educationLevel = $this->educationLevelService->createEducationLevel($request->validated());
        return ApiResponseService::success("Level Created Succefully", $educationLevel, null, 201);
    }

    public function update_education_levels(UpdateEducationLevelRequest $request, string $education_level_id)
    {

        $updateEducationLevel = $this->educationLevelService->updateEducationLevel($request->validated(), $education_level_id);
        return ApiResponseService::success("Education Level Update Sucessfully", $updateEducationLevel, null, 200);
    }

    public function delete_education_levels(Request $request, string $education_level_id)
    {
        $deleteEducationLevel = $this->educationLevelService->deleteEducationLevel($education_level_id);
        return ApiResponseService::success("Education Level Deleted Sucessfully", $deleteEducationLevel, null, 200);
    }

    public function get_all_education_leves(Request $request)
    {
        $educationLevel = $this->educationLevelService->getEducationLevels();
        return ApiResponseService::success("Education Levels Fetched Succefully", $educationLevel, null, 200);
    }
}
