<?php

namespace App\Http\Controllers;
use App\Models\Department;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Depends;

class departmentController extends Controller
{
    //
    public function create_school_department(Request $request){
         $request->validate([
            'school_branches_id' => 'required|string',
            'department_name' => 'required|string',
            'HOD' => 'string'
         ]);

         $department = new Department();

         $department->school_branches_id = $request->school_branches_id;
         $department->department_name = $request->department_name;
         $department->HOD = $request->HOD;

         $department->save();

         return response()->json(['message' => 'Department created sucessfully'], 200);
    }

    public function delete_school_department(Request $request, $department_id){
          $department = Department::find($department_id);
          if(!$department){
            return response()->json(['message' => 'Department not found'], 404);
          }
          $department->delete();
          return response()->json(['message' => 'Department deleted sucessfully'], 200);
    }

    public function update_school_department(Request $request, $department_id){
         $department = Department::find($department_id);
         if(!$department){
            return response()->json(['message' => 'Department not found'], 200);
         }
         $department_data = $request->all();
         $department_data = array_filter($department_data);
         $department->fill($department_data);

         $department->save();

         return response()->json(['message' => 'Department updated sucessfully'], 200);
    }

    public function get_all_school_department_with_school_branches(Request $request){
        $eager_loaded_data_of_department_with_school_branch = Department::with('schoolbranches');
        return response()->json(['department' => $eager_loaded_data_of_department_with_school_branch], 200);
    }

    public function get_all_department_without_school_branches(Request $request){
        $list_of_all_departments = Department::all();
        return response()->json(['department' => $list_of_all_departments], 200);
    }


}
