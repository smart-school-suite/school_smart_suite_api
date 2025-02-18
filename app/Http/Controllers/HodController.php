<?php

namespace App\Http\Controllers;
use App\Http\Requests\HodRequest;
use App\Services\ApiResponseService;
use App\Services\HodService;
use Illuminate\Http\Request;

class HodController extends Controller
{
    //
    protected HodService $hodService;
    public function __construct(HodService $hodService){
        $this->hodService = $hodService;
    }

    public function assignHeadOfDepartment(HodRequest $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $assignHod = $this->hodService->assignHeadOfDepartment($request->validated(), $currentSchool);
        return ApiResponseService::success("Head of Department Assigned Succesfully", $assignHod, null, 201);
    }

    public function getHods(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $getHods = $this->hodService->getAssignedHods($currentSchool);
        return ApiResponseService::success("Head of department Fetched Sucessfully", $getHods, null, 200);
    }

    public function removeHod(Request $request, string $hodId){
        $currentSchool = $request->attributes->get("currentSchool");
        $removeHod = $this->hodService->removeHod($hodId, $currentSchool);
        return ApiResponseService::success("Head of department Removed Successfully", $removeHod, null, 200);
    }
}
