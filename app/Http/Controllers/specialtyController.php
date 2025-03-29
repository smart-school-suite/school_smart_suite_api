<?php

namespace App\Http\Controllers;


use App\Models\Specialty;
use App\Http\Requests\SpecailtyRequest;
use App\Http\Requests\UpdateSpecailtyRequest;
use App\Services\ApiResponseService;
use App\Services\SpecailtyService;
use App\Http\Resources\SpecialtyResource;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    //
    protected SpecailtyService $specailtyService;
    public function __construct(SpecailtyService $specailtyService){
            $this->specailtyService = $specailtyService;
    }
    public function createSpecialty(SpecailtyRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createSpecailty = $this->specailtyService->createSpecialty($request->validated(), $currentSchool);
        return ApiResponseService::success("specialty created sucessfully", $createSpecailty, null, 200);
    }


    public function deleteSpecialty(Request $request, $specialty_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSpecailty = $this->specailtyService->deleteSpecailty($currentSchool, $specialty_id);
        return ApiResponseService::success("Specailty Deleted Sucessfully", $deleteSpecailty, null,200);
    }

    public function updateSpecialty(UpdateSpecailtyRequest $request, $specialty_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateSpecailty = $this->specailtyService->updateSpecialty( $request->validated(), $currentSchool, $specialty_id);
        return ApiResponseService::success("Specailty Updated Sucessfully", $updateSpecailty, null,200);
    }


    //define resoource
    public function getSpecialtiesBySchoolBranch(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getSpecialties = $this->specailtyService->getSpecailties($currentSchool);
        return ApiResponseService::success("Specialty Data Fetched Successfully",  SpecialtyResource::collection($getSpecialties), null, 200);

    }

     //define resource
    public function getSpecialtyDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty_id = $request->route('specialty_id');
        $specailtyDetails = $this->specailtyService->getSpecailtyDetails($currentSchool, $specialty_id);
        return ApiResponseService::success("specialty detials fetched succefully", $specailtyDetails, null,200);
    }

    public function activateSpecialty($specialtyId){
        $activateSpecialty = $this->specailtyService->activateSpecialty($specialtyId);
        return ApiResponseService::success("Specialty Activated Successfully", $activateSpecialty, null, 200);
    }

    public function deactivateSpecialty($specialtyId){
        $deactivateSpecialty = $this->specailtyService->deactivateSpecialty($specialtyId);
        return ApiResponseService::success("Specialty Deactivated Successfully", $deactivateSpecialty, null, 200);
    }
}
