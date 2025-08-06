<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\SpecailtyService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Specialty\CreateSpecialtyRequest;
use App\Http\Requests\Specialty\UpdateSpecialtyRequest;
use App\Http\Requests\Specialty\SpecialtyIdRequest;
use App\Http\Requests\Specialty\BulkUpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpecialtyController extends Controller
{
    //
    protected SpecailtyService $specailtyService;
    public function __construct(SpecailtyService $specailtyService)
    {
        $this->specailtyService = $specailtyService;
    }
    public function createSpecialty(CreateSpecialtyRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createSpecailty = $this->specailtyService->createSpecialty($request->validated(), $currentSchool);
        return ApiResponseService::success("specialty created sucessfully", $createSpecailty, null, 200);
    }
    public function deleteSpecialty(Request $request, $specialtyId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSpecailty = $this->specailtyService->deleteSpecailty($currentSchool, $specialtyId);
        return ApiResponseService::success("Specialty Deleted Sucessfully", $deleteSpecailty, null, 200);
    }
    public function updateSpecialty(UpdateSpecialtyRequest $request, $specialtyId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateSpecailty = $this->specailtyService->updateSpecialty($request->validated(), $currentSchool, $specialtyId);
        return ApiResponseService::success("Specialty Updated Sucessfully", $updateSpecailty, null, 200);
    }
    public function getSpecialtiesBySchoolBranch(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getSpecialties = $this->specailtyService->getSpecailties($currentSchool);
        return ApiResponseService::success("Specialty Data Fetched Successfully",  SpecialtyResource::collection($getSpecialties), null, 200);
    }
    public function getSpecialtyDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialtyId = $request->route('specialtyId');
        $specailtyDetails = $this->specailtyService->getSpecailtyDetails($currentSchool, $specialtyId);
        return ApiResponseService::success("specialty details fetched succefully", $specailtyDetails, null, 200);
    }
    public function activateSpecialty($specialtyId)
    {
        $activateSpecialty = $this->specailtyService->activateSpecialty($specialtyId);
        return ApiResponseService::success("Specialty Activated Successfully", $activateSpecialty, null, 200);
    }
    public function deactivateSpecialty($specialtyId)
    {
        $deactivateSpecialty = $this->specailtyService->deactivateSpecialty($specialtyId);
        return ApiResponseService::success("Specialty Deactivated Successfully", $deactivateSpecialty, null, 200);
    }
    public function bulkDeactivateSpecialty(SpecialtyIdRequest $request)
    {
        try {
            $bulkDeactivateSpecialty = $this->specailtyService->bulkDeactivateSpecialty($request->specialtyIds);
            return ApiResponseService::success("Specialty Deactived Succesfully", $bulkDeactivateSpecialty, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkActivateSpecialty(SpecialtyIdRequest $request)
    {
        try {
            $bulkActivateSpecialty = $this->specailtyService->bulkActivateSpecialty($request->specialtyIds);
            return ApiResponseService::success("Specialty activated Succesfully", $bulkActivateSpecialty, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteSpecialty(SpecialtyIdRequest $request)
    {
        try {
            $bulkDeleteSpecialty = $this->specailtyService->bulkDeleteSpecialty($request->specialtyIds);
            return ApiResponseService::success("Specialty Deleted Succesfully", $bulkDeleteSpecialty, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkUdateSpecialty(BulkUpdateSpecialtyRequest $request)
    {
        try {
            $bulkUpdateSpecialty = $this->specailtyService->bulkUpdateSpecialty($request->specialties);
            return ApiResponseService::success("Specialty Updated Successfully", $bulkUpdateSpecialty, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
