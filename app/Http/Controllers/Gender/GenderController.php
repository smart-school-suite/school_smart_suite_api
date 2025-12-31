<?php

namespace App\Http\Controllers\Gender;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gender\CreateGenderRequest;
use App\Http\Requests\Gender\UpdateGenderRequest;
use App\Models\Gender;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Gender\GenderService;

class GenderController extends Controller
{
    protected GenderService $genderService;
    public function __construct(GenderService $genderService)
    {
        $this->genderService = $genderService;
    }
    public function createGender(CreateGenderRequest $request)
    {
        $createGender = $this->genderService->createGender($request->validated());
        return ApiResponseService::success(
            "Gender Created Successfully",
            $createGender,
            null,
            201
        );
    }

    public function deleteGender($genderId)
    {
        $deleteGender = $this->genderService->deleteGender($genderId);
        return ApiResponseService::success(
            "Gender Deleted Successfully",
            $deleteGender,
            null,
            200
        );
    }

    public function updateGender(UpdateGenderRequest $request, $genderId)
    {
        $updateGender = $this->genderService->updateGender($request->validated(), $genderId);
        return ApiResponseService::success(
            "Gender Updated Successfully",
            $updateGender,
            null,
            200
        );
    }

    public function getActiveGender()
    {
        $activeGender = $this->genderService->getActiveGender();
        return ApiResponseService::success(
            "Active Gender Fetched Successfully",
            $activeGender,
            null,
            200
        );
    }

    public function getAllGender()
    {
        $gender = $this->genderService->getAllGender();
        return ApiResponseService::success(
            "Gender Fetched Successfully",
            $gender,
            null,
            200
        );
    }

    public function activateGender($genderId)
    {
        $activateGender = $this->genderService->activateGender($genderId);
        return ApiResponseService::success(
            "Gender Activated Successfully",
            $activateGender,
            null,
            200
        );
    }

    public function deactivateGender($genderId)
    {
        $deactivateGender = $this->genderService->deactivateGender($genderId);
        return ApiResponseService::success(
            "Gender Deactivated Successfully",
            $deactivateGender,
            null,
            200
        );
    }
}
