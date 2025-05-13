<?php

namespace App\Http\Controllers;

use App\Models\Edumanageadmin;
use Illuminate\Http\Request;

class EdumanageAdminController extends Controller
{
    //
    public function getAppAdmins(Request $request){
        $appAdmin = Edumanageadmin::all();
        if($appAdmin->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'No admin records found'
            ], 400);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'admin data fetched sucessfully',
            'admins' => $appAdmin
        ], 200);
    }

    public function deleteAppAdmin($appAdminId){
        $appAdmin = Edumanageadmin::find($appAdminId);
        if(!$appAdmin){
            return response()->json([
                'status' => 'ok',
                'message' => 'Admin not found'
            ], 409);
        }

        $appAdmin->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Admin deleted succesfully'
        ], 200);
    }

    public function updateAppAdmin(Request $request, $appAdminId){
        $appAdmin = Edumanageadmin::find($appAdminId);
        if(!$appAdmin){
            return response()->json([
                'status' => 'ok',
                'message' => 'Admin not found'
            ], 409);
        }

        $update_data = $request->all();
        $update_data = array_filter($update_data);
        $appAdmin->fill($update_data);
        $appAdmin->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Admin updated sucessfully'
        ], 200);
    }
}
