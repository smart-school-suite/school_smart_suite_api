<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hall\AssignSpecialtyHallRequest;
use App\Http\Requests\Hall\RemoveAssigedSpecialtyHallRequest;
use App\Services\ApiResponseService;
use App\Services\Hall\SpecialtyHallService;
use Illuminate\Http\Request;

class SpecialtyHallController extends Controller
{
    protected SpecialtyHallService $specialtyHallService;

    public function __construct(SpecialtyHallService $specialtyHallService)
    {
        $this->specialtyHallService = $specialtyHallService;
    }

    public function assignHallToSpecialty(AssignSpecialtyHallRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $authAdmin = $this->resolveUser();
        $assignHall = $this->specialtyHallService->assignHallToSpecialty($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Hall Assigned Successfully", $assignHall, null, 200);
    }

    public function getAssignableHalls(Request $request, $specialtyId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $assignableHalls = $this->specialtyHallService->getAvailableAssignableHalls($currentSchool, $specialtyId);
        return ApiResponseService::success("Assignable Halls Fetched Successfully", $assignableHalls, null, 200);
    }

    public function getAssignedHalls(Request $request, $specialtyId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $assignedHalls = $this->specialtyHallService->getAssignedHalls($currentSchool, $specialtyId);
        return ApiResponseService::success("Assigned Halls Fetched Successfully", $assignedHalls, null, 200);
    }

    public function removeAssignedHalls(RemoveAssigedSpecialtyHallRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $authAdmin = $this->resolveUser();
        $removedAssignedHall = $this->specialtyHallService->removeAssignedHalls($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Specialty Assiged Hall Removed Successfully", $removedAssignedHall, null, 200);
    }

    public function removeAllAssignedHalls(Request $request, $specialtyId){
        $currentSchool = $request->attributes->get("currentSchool");
        $authAdmin = $this->resolveUser();
        $removeAllAssignedHalls = $this->specialtyHallService->removeAllAssignedHalls($currentSchool, $specialtyId, $authAdmin);
        return ApiResponseService::success("All Assigned Specialty Halls Removed Successfully", $removeAllAssignedHalls, null, 200);
    }

    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
