<?php

namespace App\Http\Controllers;
use App\Models\Department;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Depends;

class departmentController extends Controller
{
    //
    public function create_school_department(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
         $request->validate([
            'department_name' => 'required|string',
            'HOD' => 'string'
         ]);

         $department = new Department();

         $department->school_branch_id = $currentSchool->id;
         $department->department_name = $request->department_name;
         $department->HOD = $request->HOD;

         $department->save();

         return response()->json([
           'status' => 'ok',
           'message' => 'Department created sucessfully',
           'department' => $department
         ], 200);
    }

    public function delete_school_department(Request $request, $department_id){
          $currentSchool = $request->attributes->get('currentSchool');
          $department = Department::Where('school_branch', $currentSchool->id)->find($department_id);
          if(!$department){
            return response()->json([
              'status' => 'ok',
              'message' => 'Department not found'
            ], 404);
          }
          $department->delete();
          return response()->json([
            'status' => 'ok',
            'message' => 'Department deleted sucessfully',
            'department' => $department
          ], 200);
    }

    public function update_school_department(Request $request, $department_id){
        $currentSchool = $request->attributes->get('currentSchool');
          $department = Department::Where('school_branch', $currentSchool->id)->find($department_id);
          if(!$department){
            return response()->json([
              'status' => 'ok',
              'message' => 'Department not found'
            ], 404);
          }
         $department_data = $request->all();
         $department_data = array_filter($department_data);
         $department->fill($department_data);

         $department->save();

         return response()->json([
            'status' => 'ok',
            'message' => 'Department updated sucessfully',
            'department' => $department
         ], 200);
    }

    public function get_all_school_department_with_school_branches(Request $request){
      $currentSchool = $request->attributes->get('currentSchool');
      
      // Fetch the departments as a collection
      $departments = Department::where('school_branch_id', $currentSchool->id)->get(); 
  
      // Check if the collection is empty
      if ($departments->isEmpty()) {
          return response()->json([
              'status' => 'ok',
              'message' => 'no records found'
          ], 400);
      }
  
      return response()->json([
          'status' => 'ok',
          'message' => 'records fetched successfully',
          'department' => $departments // Return the actual records
      ], 200);
  }
    public function get_all_department_without_school_branches(Request $request){
        $list_of_all_departments = Department::all();
        if($list_of_all_departments->isEmpty()){
            return response()->json([
              'status' => 'ok',
              'message' => 'no records found'
            ], 400);
        }
        return response()->json([
          'status' => 'ok',
          'message' => 'department fetched succefully',
          'department' => $list_of_all_departments
        ], 200);
    }

    public function department_details(Request $request){
       $currentSchool = $request->attributes->get('currentSchool');
      $department_id = $request->route("department_id");
       $find_department = Department::find($department_id);
       if(!$find_department){
          return response()->json([
             "status" => "error",
             "message" => "Department not found", 
          ], 400);
       }
      
       $department_details = Department::where("school_branch_id", $currentSchool->id)
                                       ->where("id", $department_id)->get();
            
        return response()->json([
           "status" => "ok",
           "message" => "Department fetched successfully",
           "department_details" => $department_details
        ], 200);
    }


}
