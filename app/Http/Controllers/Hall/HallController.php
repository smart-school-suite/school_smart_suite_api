<?php

namespace App\Http\Controllers\Hall;

use App\Services\Import\ImportJobDispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hall\BulkUpdateHallRequest;
use App\Http\Requests\Hall\CreateHallRequest;
use App\Http\Requests\Hall\ImportHallRequest;
use App\Http\Requests\Hall\UpdateHallRequest;
use App\Http\Requests\Hall\ValidateHallIdRequest;
use App\Jobs\Hall\HallImportJob;
use App\Models\Hall;
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

    public function deleteHall(Request $request, string $hallId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteHall = $this->hallService->deleteHall($currentSchool, $hallId, $authAdmin);
        return ApiResponseService::success("Hall Deleted Successfully", $deleteHall, null, 200);
    }

    public function updateHall(UpdateHallRequest $request, string $hallId)
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

    public function activateHall(Request $request, string $hallId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $activateHall = $this->hallService->activateHall($currentSchool, $hallId, $authAdmin);
        return ApiResponseService::success("Hall Activated Successfully", $activateHall, null, 200);
    }

    public function deactivateHall(Request $request, string $hallId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $deactivateHall = $this->hallService->deactivateHall($currentSchool, $hallId, $authAdmin);
        return ApiResponseService::success("Hall Deactivated Successfully", $deactivateHall, null, 200);
    }

    public function getHallDetails(Request $request, string $hallId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $hall = $this->hallService->getHallDetails($currentSchool, $hallId);
        return ApiResponseService::success("Hall Details Fetched Successfully", $hall, null, 200);
    }

    public function bulkDeleteHall(ValidateHallIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deletedHalls = $this->hallService->bulkDeleteHall($currentSchool, $request->validated()['hallIds'], $authAdmin);
        return ApiResponseService::success("Halls Deleted Successfully", $deletedHalls, null, 200);
    }

    public function bulkActivateHall(ValidateHallIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $activatedHalls = $this->hallService->bulkActivateHall($currentSchool, $request->validated()['hallIds'], $authAdmin);
        return ApiResponseService::success("Halls Activated Successfully", $activatedHalls, null, 200);
    }

    public function bulkDeactivateHall(ValidateHallIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deactivatedHalls = $this->hallService->bulkDeactivateHall($currentSchool, $request->validated()['hallIds'], $authAdmin);
        return ApiResponseService::success("Halls Deactivated Successfully", $deactivatedHalls, null, 200);
    }

    public function bulkUpdateHall(BulkUpdateHallRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updatedHalls = $this->hallService->bulkUpdateHall($currentSchool, $request->validated()['halls'], $authAdmin);
        return ApiResponseService::success("Halls Updated Successfully", $updatedHalls, null, 200);
    }

    public function importHall(ImportHallRequest $request)
    {
        $job = app(ImportJobDispatcher::class)->dispatch(
            HallImportJob::class,
            $request->file('file'),
            $request->mapping(),
            $request->attributes->get('currentSchool'),
            $this->resolveUser()
        );

        return ApiResponseService::success('Hall import started', $job, null, 202);
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
