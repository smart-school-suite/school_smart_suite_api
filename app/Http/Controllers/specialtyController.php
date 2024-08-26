<?php

namespace App\Http\Controllers;

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
            'department_id' => 'required|string',
            'specialty_name' => 'required|string',
            'registration_fee' => 'required|decimal:1,1000000',
            'school_fee' => 'required|decimal:1, 1000000',
            'level_id' => 'required|string'
        ]);

        $specialty = new Specialty();
        $specialty->school_branch_id = $currentSchool->id;
        $specialty->department_id = $request->department_id;
        $specialty->specialty_name = $request->specialty_name;
        $specialty->registration_fee = $request->registration_fee;
        $specialty->school_fee = $request->school_fee;
        $specialty->level_id = $request->level_id;

        $specialty->save();

        return response()->json(['message' => 'specialty created sucessfully'], 200);
    }


    public function delete_school_specialty(Request $request, $specialty_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty = Specialty::Where('school_branch_id', $currentSchool->id)->find($specialty_id);

        if (!$specialty) {
            return response()->json(['message' => 'specialty not found'], 404);
        }

        $specialty->delete();

        return response()->json(['message' => 'Specialty deleted successfully'], 200);
    }

    public function update_school_specialty(Request $request, $specialty_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty = Specialty::Where('school_branch_id', $currentSchool->id)->find($specialty_id);
        if (!$specialty) {
            return response()->json(['message' => 'specialty not found'], 404);
        }

        $specialty_data = $request->all();
        $specialty_data = array_filter($specialty_data);
        $specialty->fill($specialty_data);

        $specialty->save();
        return response()->json(['message' => 'specialty updated sucessfully'], 200);
    }


    public function get_all_school_specialty(Request $request)
    {
        $specialty_data = Specialty::all();
        return response()->json(['specialty' => $specialty_data], 200);
    }

    public function get_all_tenant_School_specailty_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty_data = Specialty::Where('school_branch_id', $currentSchool->id)->get();
        return response()->json(['specialties', $specialty_data], 200);
    }
}
