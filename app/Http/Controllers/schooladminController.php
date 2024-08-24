<?php

namespace App\Http\Controllers;
use App\Models\Schooladmin;
use Illuminate\Http\Request;


class schooladminController extends Controller
{
    //
    public function update_school_admin(Request $request, $school_admin_id){
        $currentSchool = $request->attributes->get('currentSchool');

        $school_admin = Schooladmin::Where('school_id', $currentSchool->id)->find($school_admin_id);
        if(!$school_admin){
            return response()->json(['message' => 'Admin not found'], 409);
        }

        $school_admin_data = $request->all();
        $school_admin_data = array_filter($school_admin_data);
        $school_admin->fill($school_admin_data);

        return response()->json(['message' => 'admin updated succesfully'], 201);
    }

    public function delete_school_admin(Request $request, $school_admin_id){
        $currentSchool = $request->attributes->get('currentSchool');

        $school_admin = Schooladmin::Where('school_id', $currentSchool->id)->find($school_admin_id);
        if(!$school_admin){
            return response()->json(['message' => 'Admin not found'], 409);
        }

        $school_admin->delete();

        return response()->json(['message' => 'Admin deleted sucessfully'], 201);
    }

    public function get_all_school_admins_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $school_admins = Schooladmin::Where('school_id', $currentSchool->id);

        return response()->json(['school_admin_data' => $school_admins], 201);
        
    }

}
