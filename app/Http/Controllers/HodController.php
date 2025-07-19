<?php

namespace App\Http\Controllers;
use App\Http\Requests\HodRequest;
use App\Http\Requests\Hod\CreateHodRequest;
use App\Services\ApiResponseService;
use App\Services\HodService;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\HodResource;
use Illuminate\Http\Request;

class HodController extends Controller
{
    //
    protected HodService $hodService;
    public function __construct(HodService $hodService){
        $this->hodService = $hodService;
    }

    public function assignHeadOfDepartment(CreateHodRequest $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $assignHod = $this->hodService->assignHeadOfDepartment($request->validated(), $currentSchool);
        return ApiResponseService::success("Head of Department Assigned Succesfully", $assignHod, null, 201);
    }

    public function getHods(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $getHods = $this->hodService->getAssignedHods($currentSchool);
        return ApiResponseService::success("Head of department Fetched Sucessfully", $getHods, null, 200);
    }

    public function getHodDetails(Request $request, $hodId){
        $currentSchool = $request->attributes->get('currentSchool');
        $hodDetails = $this->hodService->getHodDetails( $hodId, $currentSchool);
        return ApiResponseService::success("Head of department Details Fetched Successfully", $hodDetails, null, 200);
    }

    public function removeHod(Request $request, string $hodId){
        $currentSchool = $request->attributes->get("currentSchool");
        $removeHod = $this->hodService->removeHod($hodId, $currentSchool);
        return ApiResponseService::success("Head of department Removed Successfully", $removeHod, null, 200);
    }
    public function bulkRemoveHod(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        try{
             $this->hodService->bulkRemoveHod($request->hods, $currentSchool);
            return ApiResponseService::success("HOD Removed Succesfully", null, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
