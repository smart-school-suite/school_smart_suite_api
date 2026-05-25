<?php

namespace App\Http\Controllers\Invigilator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invigilator\CreateInvigilatorRequest;
use App\Http\Requests\Invigilator\RemoveInvigilatorRequest;
use App\Services\ApiResponseService;
use App\Services\Invigilator\InvigilatorService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InvigilatorController extends Controller
{
    protected InvigilatorService $invigilatorService;
    public function __construct(InvigilatorService $invigilatorService)
    {
        $this->invigilatorService = $invigilatorService;
    }

    public function createInvigilators(CreateInvigilatorRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $createInvigilators = $this->invigilatorService->createInvigilator($request->validated(), $currentSchool);
        return ApiResponseService::success("Invigilators Created Successfully", $createInvigilators, null, 201);
    }
    public function removeInvigilators(RemoveInvigilatorRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $removeInvigilators = $this->invigilatorService->removeInvigilators($request->validated(), $currentSchool);
        return ApiResponseService::success("Invigilators Removed Successfully", $removeInvigilators, null, 200);
    }
    public function getPotInvigilators(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $potInvigilators = $this->invigilatorService->getPotentialInvigilators($currentSchool, $this->getAuthenticatedUser());
        return ApiResponseService::success("Potential Invigilators Fetched Successfully", $potInvigilators, null, 200);
    }
    public function getInvigilators(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $invigilators = $this->invigilatorService->getInvigilators($currentSchool, $this->getAuthenticatedUser());
        return ApiResponseService::success("Invigilators Fetched Successfully", $invigilators, null, 200);
    }
    private function getAuthenticatedUser(): array
    {
        $user = Auth::user();

        if ($user instanceof Model) {
            return [
                'userId' => $user->id,
                'userType' => get_class($user),
                'authUser' => $user
            ];
        }

        return [
            'userId' => null,
            'userType' => null,
            'authUser' => $user
        ];
    }
}
