<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\SpecialtyService;
use App\Http\Requests\Specialty\CreateSpecialtyRequest;
use App\Http\Requests\Specialty\UpdateSpecialtyRequest;
use App\Http\Requests\Specialty\SpecialtyIdRequest;
use App\Http\Requests\Specialty\BulkUpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use Exception;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    //
    protected SpecialtyService $specialtyService;
    public function __construct(SpecialtyService $specialtyService)
    {
        $this->specialtyService = $specialtyService;
    }
    public function createSpecialty(CreateSpecialtyRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createSpecailty = $this->specialtyService->createSpecialty($request->validated(), $currentSchool);
        return ApiResponseService::success("specialty created sucessfully", $createSpecailty, null, 200);
    }
    public function deleteSpecialty(Request $request, $specialtyId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSpecailty = $this->specialtyService->deleteSpecialty($currentSchool, $specialtyId);
        return ApiResponseService::success("Specialty Deleted Sucessfully", $deleteSpecailty, null, 200);
    }
    public function updateSpecialty(UpdateSpecialtyRequest $request, $specialtyId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateSpecailty = $this->specialtyService->updateSpecialty($request->validated(), $currentSchool, $specialtyId);
        return ApiResponseService::success("Specialty Updated Sucessfully", $updateSpecailty, null, 200);
    }
    public function getSpecialtiesBySchoolBranch(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getSpecialties = $this->specialtyService->getSpecialties($currentSchool);
        return ApiResponseService::success("Specialty Data Fetched Successfully",  SpecialtyResource::collection($getSpecialties), null, 200);
    }
    public function getSpecialtyDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialtyId = $request->route('specialtyId');
        $specailtyDetails = $this->specialtyService->getSpecailtyDetails($currentSchool, $specialtyId);
        return ApiResponseService::success("specialty details fetched succefully", $specailtyDetails, null, 200);
    }
    public function activateSpecialty($specialtyId)
    {
        $activateSpecialty = $this->specialtyService->activateSpecialty($specialtyId);
        return ApiResponseService::success("Specialty Activated Successfully", $activateSpecialty, null, 200);
    }
    public function deactivateSpecialty($specialtyId)
    {
        $deactivateSpecialty = $this->specialtyService->deactivateSpecialty($specialtyId);
        return ApiResponseService::success("Specialty Deactivated Successfully", $deactivateSpecialty, null, 200);
    }
    public function bulkDeactivateSpecialty(SpecialtyIdRequest $request)
    {
        try {
            $bulkDeactivateSpecialty = $this->specialtyService->bulkDeactivateSpecialty($request->specialtyIds);
            return ApiResponseService::success("Specialty Deactived Succesfully", $bulkDeactivateSpecialty, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkActivateSpecialty(SpecialtyIdRequest $request)
    {
        try {
            $bulkActivateSpecialty = $this->specialtyService->bulkActivateSpecialty($request->specialtyIds);
            return ApiResponseService::success("Specialty activated Succesfully", $bulkActivateSpecialty, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteSpecialty(SpecialtyIdRequest $request)
    {
        try {
            $bulkDeleteSpecialty = $this->specialtyService->bulkDeleteSpecialty($request->specialtyIds);
            return ApiResponseService::success("Specialty Deleted Succesfully", $bulkDeleteSpecialty, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkUdateSpecialty(BulkUpdateSpecialtyRequest $request)
    {
        try {
            $bulkUpdateSpecialty = $this->specialtyService->bulkUpdateSpecialty($request->specialties);
            return ApiResponseService::success("Specialty Updated Successfully", $bulkUpdateSpecialty, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
