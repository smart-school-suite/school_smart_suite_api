<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hall\CreateHallRequest;
use App\Http\Requests\Hall\UpdateHallRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Hall\HallService;

class HallController extends Controller
{
    protected HallService $hallService;
    public function __construct(HallService $hallService)
    {
        $this->hallService = $hallService;
    }

    public function createHall(CreateHallRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $createHall = $this->hallService->createHall($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Hall Created Successfully", $createHall, null, 200);
    }

    public function deleteHall(Request $request, $hallId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteHall = $this->hallService->deleteHall($currentSchool, $hallId, $authAdmin);
        return ApiResponseService::success("Hall Deleted Successfully", $deleteHall, null, 200);
    }

    public function updateHall(UpdateHallRequest $request, $hallId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updateHall = $this->hallService->updateHall($currentSchool, $request->validated(), $hallId, $authAdmin);
        return ApiResponseService::success("Hall Updated Successfully", $updateHall, null, 200);
    }

    public function getAllHalls(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $halls = $this->hallService->getAllHalls($currentSchool);
        return ApiResponseService::success("Halls Fetched Successfully", $halls, null, 200);
    }

    public function getActiveHalls(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $activeHalls = $this->hallService->getActiveHalls($currentSchool);
        return ApiResponseService::success("Active Halls Fetched Successfully", $activeHalls, null, 200);
    }

    public function activateHall(Request $request, $hallId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $activateHall = $this->hallService->activateHall($currentSchool, $hallId, $authAdmin);
        return ApiResponseService::success("Hall Activated Successfully", $activateHall, null, 200);
    }

    public function deactivateHall(Request $request, $hallId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $deactivateHall = $this->hallService->deactivateHall($currentSchool, $hallId, $authAdmin);
        return ApiResponseService::success("Hall Deactivated Successfully", $deactivateHall, null, 200);
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
