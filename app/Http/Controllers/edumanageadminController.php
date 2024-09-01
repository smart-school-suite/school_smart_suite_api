<?php

namespace App\Http\Controllers;

use App\Models\Edumanageadmin;
use Illuminate\Http\Request;

class edumanageadminController extends Controller
{
    //
    public function get_all_eduamage_admins(Request $request){
        $edu_manage_admin_data = Edumanageadmin::all();
        return response()->json(['admins' => $edu_manage_admin_data], 200);
    }

    public function delete_edumanage_admin(Request $request, $edumanage_admin_id){
        $edu_manage_admin_data = Edumanageadmin::find($edumanage_admin_id);
        if(!$edu_manage_admin_data){
            return response()->json(['message' => 'Admin not found'], 409);
        }

        $edu_manage_admin_data->delete();

        return response()->json(['message' => 'Admin deleted succesfully'], 200);
    }

    public function update_edumanage_admin(Request $request, $edumanage_admin_id){
        $edu_manage_admin_data = Edumanageadmin::find($edumanage_admin_id);
        if(!$edu_manage_admin_data){
            return response()->json(['message' => 'Admin not found'], 409);
        }

        $update_data = $request->all();
        $update_data = array_filter($update_data);
        $edu_manage_admin_data->fill($update_data);
        $edu_manage_admin_data->save();

        return response()->json(['message' => 'Admin updated sucessfully'], 200);
    }
}
