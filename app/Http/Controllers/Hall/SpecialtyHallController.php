<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hall\AssignSpecialtyHallRequest;
use App\Services\ApiResponseService;
use App\Services\Hall\SpecialtyHallService;
use Illuminate\Http\Request;

class SpecialtyHallController extends Controller
{
    protected SpecialtyHallService $specialtyHallService;

    public function __construct(SpecialtyHallService $specialtyHallService){
         $this->specialtyHallService = $specialtyHallService;
    }

    public function assignHallToSpecialty(AssignSpecialtyHallRequest $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $assignHall = $this->specialtyHallService->assignHallToSpecialty($currentSchool, $request->validated());
         return ApiResponseService::success("Hall Assigned Successfully", $assignHall, null, 200);
    }

    public function getAvailableAssignableHalls(Request $request, $specialtyId){
         $currentSchool = $request->attributes->get("currentSchool");
         $assignableHalls = $this->specialtyHallService->getAvailableAssignableHalls($currentSchool, $specialtyId);
         return ApiResponseService::success("Assignable Halls Fetched Successfully", $assignableHalls, null, 200);
    }

    public function getAssignedHalls(Request $request, $specialtyId){
        $currentSchool = $request->attributes->get("currentSchool");
        $assignedHalls = $this->specialtyHallService->getAssignedHalls($currentSchool, $specialtyId);
        return ApiResponseService::success("Assigned Halls Fetched Successfully", $assignedHalls, null, 200);
    }

    public function removeAssignedHalls(Request $request, $specialtyHallId){
         $currentSchool = $request->attributes->get("currentSchool");
         $removedAssignedHall = $this->specialtyHallService->removeAssignedHalls($currentSchool, $specialtyHallId);
         return ApiResponseService::success("Specialty Assiged Hall Removed Successfully", $removedAssignedHall, null, 200);
    }
}
