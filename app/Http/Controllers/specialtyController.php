<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Specialty;
use App\Models\Tenantspecialty;
use Illuminate\Http\Request;

class specialtyController extends Controller
{
    //

    public function create_school_speciality(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'specialty_name' => 'required|string',
            'department_id' => 'required|string',
            'registration_fee' => 'required|decimal:0, 2',
            'school_fee' => 'required|decimal:0, 2',
            'level_id' => 'required|string'
        ]);
         
        $check_department = Department::where('school_branch_id', $currentSchool->id)->find($request->department_id);
        if(!$check_department){
            return response()->json(['message' => 'This deparment was not found'], 409);
        }
        $specialty = new Specialty();
        $specialty->school_branch_id = $currentSchool->id;
        $specialty->department_id = $request->department_id;
        $specialty->specialty_name = $request->specialty_name;
        $specialty->registration_fee = $request->registration_fee;
        $specialty->school_fee = $request->school_fee;
        $specialty->level_id = $request->level_id;

        $specialty->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'specialty created sucessfully',
            'created_specialty' => $specialty
        ], 200);
    }


    public function delete_school_specialty(Request $request, $specialty_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty = Specialty::Where('school_branch_id', $currentSchool->id)->find($specialty_id);

        if (!$specialty) {
            return response()->json([
                'status' => 'ok',
                'message' => 'specialty not found'
            ], 404);
        }

        $specialty->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Specialty deleted successfully',
            'created_specialty' => $specialty
        ], 200);
    }

    public function update_school_specialty(Request $request, $specialty_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty = Specialty::Where('school_branch_id', $currentSchool->id)->find($specialty_id);
        if (!$specialty) {
            return response()->json([
                'status' => 'ok',
                'message' => 'specialty not found'
            ], 404);
        }

        $specialty_data = $request->all();
        $specialty_data = array_filter($specialty_data);
        $specialty->fill($specialty_data);
        $specialty->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'specialty updated sucessfully',
            'created_specialty' => $specialty
        ], 200);
    }


    public function get_all_school_specialty(Request $request)
    {
        $specialty_data = Specialty::all();
        if($specialty_data->isEmpty()){
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

    public function get_all_tenant_School_specailty_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty_data = Specialty::Where('school_branch_id', $currentSchool->id)->with('level')->get();
        return response()->json(['specialties', $specialty_data], 200);
    }
}
