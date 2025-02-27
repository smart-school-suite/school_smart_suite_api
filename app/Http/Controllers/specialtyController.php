<?php

namespace App\Http\Controllers;


use App\Models\Specialty;
use App\Http\Requests\SpecailtyRequest;
use App\Http\Requests\UpdateSpecailtyRequest;
use App\Services\ApiResponseService;
use App\Services\SpecailtyService;
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
        $updateSpecailty = $this->specailtyService->updateSpecialty( $request->validated(), $currentSchool, $specialty_id, );
        return ApiResponseService::success("Specailty Updated Sucessfully", $updateSpecailty, null,200);
    }


    public function get_all_school_specialty()
    {
        $specialty_data = Specialty::all();
        if ($specialty_data->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'No records found'
            ], 409);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'specialties fetched succesfully',
            'specialty' => $specialty_data
        ], 200);
    }
    //define resoource
    public function get_all_tenant_School_specailty_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getSpecailty = $this->specailtyService->getSpecailties($currentSchool);
      //  foreach ($specialty_data as $specialty) {
           // $result[] = [
             //   'id' => $specialty->id,
               // 'level_name' => $specialty->level->name,
              //  'level_number' => $specialty->level->level,
              //  'specialty_name' => $specialty->specialty_name,
                //'registration_fee' => $specialty->registration_fee,
                //'school_fee' => $specialty->school_fee
           // ];
       // }
       return ApiResponseService::success("Specailties Fetched Succefully", $getSpecailty, null,200);

    }

     //define resource
    public function getSpecialtyDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty_id = $request->route('specialty_id');
        $specailtyDetails = $this->specailtyService->getSpecailtyDetails($currentSchool, $specialty_id);
        return ApiResponseService::success("specialty detials fetched succefully", $specailtyDetails, null,200);
    }
}
