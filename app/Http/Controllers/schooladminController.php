<?php

namespace App\Http\Controllers;
use App\Models\Schooladmin;
use Illuminate\Http\Request;


class schooladminController extends Controller
{
    //
    public function update_school_admin(Request $request, $school_admin_id){
        $currentSchool = $request->attributes->get('currentSchool');

        $school_admin = Schooladmin::Where('school_branch_id', $currentSchool->id)->find($school_admin_id);
        if(!$school_admin){
            return response()->json([
                'status' => 'ok',
                'message' => 'Admin not found'
            ], 409);
        }

        $school_admin_data = $request->all();
        $school_admin_data = array_filter($school_admin_data);
        $school_admin->fill($school_admin_data);
        $school_admin->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'admin updated succesfully',
            'updated_admin' => $school_admin
        ], 201);
    }

    public function delete_school_admin(Request $request, $school_admin_id){
        $currentSchool = $request->attributes->get('currentSchool');

        $school_admin = Schooladmin::Where('school_branch_id', $currentSchool->id)->find($school_admin_id);
        if(!$school_admin){
            return response()->json([
                'status' => 'error',
                'message' => 'Admin not found'
            ], 409);
        }

        $school_admin->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Admin deleted sucessfully',
            'deleted_admin' => $school_admin
        ], 201);
    }

    public function get_all_school_admins_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $school_admins = Schooladmin::Where('school_branch_id', $currentSchool->id);
        if($school_admins->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'no school admins created'
            ], 409);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'school administrators fetch sucessfull',
            'school_admin_data' => $school_admins
        ], 201);
        
    }

}
