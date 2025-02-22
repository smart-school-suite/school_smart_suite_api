<?php

namespace App\Http\Controllers;

use App\Models\Edumanageadmin;
use Illuminate\Http\Request;

class EdumanageAdminController extends Controller
{
    //
    public function get_all_eduamage_admins(Request $request){
        $edu_manage_admin_data = Edumanageadmin::all();
        if($edu_manage_admin_data->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'No admin records found'
            ], 400);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'admin data fetched sucessfully',
            'admins' => $edu_manage_admin_data
        ], 200);
    }

    public function delete_edumanage_admin(Request $request, $edumanage_admin_id){
        $edu_manage_admin_data = Edumanageadmin::find($edumanage_admin_id);
        if(!$edu_manage_admin_data){
            return response()->json([
                'status' => 'ok',
                'message' => 'Admin not found'
            ], 409);
        }

        $edu_manage_admin_data->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Admin deleted succesfully'
        ], 200);
    }

    public function update_edumanage_admin(Request $request, $edumanage_admin_id){
        $edu_manage_admin_data = Edumanageadmin::find($edumanage_admin_id);
        if(!$edu_manage_admin_data){
            return response()->json([
                'status' => 'ok',
                'message' => 'Admin not found'
            ], 409);
        }

        $update_data = $request->all();
        $update_data = array_filter($update_data);
        $edu_manage_admin_data->fill($update_data);
        $edu_manage_admin_data->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Admin updated sucessfully'
        ], 200);
    }
}
