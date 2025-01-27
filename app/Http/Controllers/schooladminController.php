<?php

namespace App\Http\Controllers;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;


class schooladminController extends Controller
{
    //
    public function update_school_admin(Request $request, ){
        $currentSchool = $request->attributes->get('currentSchool');
        $school_admin_id = $request->route("school_admin_id");
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
        $school_admins = Schooladmin::Where('school_branch_id', $currentSchool->id)->get();
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

    public function school_admin_details(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $school_admin_id = $request->route('school_admin_id');

        $find_admin = Schooladmin::find($school_admin_id);

        if(!$find_admin){
             return response()->json([
                 "status" => "error",
                 "message" => "Admin not Found"
             ], 400);
        }
        $school_admin_details = Schooladmin::where('school_branch_id', $currentSchool->id)
                                ->where('id', $school_admin_id)
                                ->get();


        return response()->json([
             "status" => "success",
             "message" => "school amdin details fetched succefully",
             "school_admin_details" => $school_admin_details
        ], 201);

    }

    public function create_School_admin_on_sign_up(Request $request){
        $request->validate([
            'name' => "required|string",
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|string',
            'employment_status' => 'required|string',
            'work_location' => 'required|string',
            'position' => 'required|string',
            'hire_date' => 'required|date',
            'salary' => 'required',
        ]);

        $new_school_admin_instance = new Schooladmin();
        $new_school_admin_instance->name = $request->name;
        $new_school_admin_instance->email = $request->email;
        $new_school_admin_instance->password = Hash::make($request->password);
        $new_school_admin_instance->role = $request->role;
        $new_school_admin_instance->employment_status = $request->employment_status;
        $new_school_admin_instance->hire_date = $request->hire_date;
        $new_school_admin_instance->work_location = $request->work_location;
        $new_school_admin_instance->position = $request->position;
        $new_school_admin_instance->salary = $request->salary;
        $new_school_admin_instance->school_branch_id = $request->school_branch_id;

        $new_school_admin_instance->save();

        return response()->json([
            'status' => "ok",
            'message' => 'School admin created succefully',
            'school_admin' => $new_school_admin_instance
        ], 200);

    }

}
