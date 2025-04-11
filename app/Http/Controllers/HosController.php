<?php

namespace App\Http\Controllers;
use App\Services\HosService;
use App\Http\Requests\HosRequest;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\HosResource;
use Illuminate\Http\Request;

class HosController extends Controller
{
    //
    protected HosService $hosService;
    public function __construct(HosService $hosService){
        $this->hosService = $hosService;
    }

    public function assignHeadOfSpecialty(HosRequest $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $assignHos = $this->hosService->assignHeadOfSpecialty($request->validated(), $currentSchool);
        return ApiResponseService::success("Hos Assigned Succesfully", $assignHos, null, 201);
    }

    public function getHeadOfSpecialty(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $getHeadOfSpecialty = $this->hosService->getAssignedHos($currentSchool);
        return ApiResponseService::success("Head of department Data fetched Sucessfully", $getHeadOfSpecialty, null, 200);
    }

    public function removeHeadOfSpecialty(Request $request, string $hosId){
        $currentSchool = $request->attributes->get("currentSchool");
        $removeHod = $this->hosService->removeHos($hosId, $currentSchool);
        return ApiResponseService::success("Head Of Department Removed Succesfully", $removeHod, null, 200);
    }

    public function getAllHos(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $getHos = $this->hosService->getAllHOS($currentSchool);
        return ApiResponseService::success("Head of Specialty Fetched Succesfully",  HosResource::collection($getHos), null, 200);
    }

    public function getHosDetails(Request $request, string $hosId){
        $getHosDetails = $this->hosService->getHosDetails($hosId);
        return ApiResponseService::success("Head of Specialty Details fetched Succesfully", $getHosDetails, null, 200);
    }

    public function bulkRemoveHos($hosIds){
        $idsArray = explode(',', $hosIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:hos,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }

        try{
            $bulkRemoveHos = $this->hosService->bulkRemoveHos($idsArray);
            return ApiResponseService::success("HOD Removed Succesfully", $bulkRemoveHos, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
