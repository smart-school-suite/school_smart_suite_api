<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Student;

class studentController extends Controller
{
    //

    public function get_all_students_in_school(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $students = Student::where('school_branch_id', $currentSchool->id)->with('parents')->get();
        return response()->json([
            'status' => 'ok',
            'message' => 'student records fetched sucessfully',
            'students' => $students
        ], 200);
    }

    public function delete_Student_Scoped(Request $request, $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data_scoped = Student::where('school_branch_id', $currentSchool->id)
            ->find($student_id);
        if (!$student_data_scoped) {
            return response()->json([
                'status' => 'error',
                'message' => 'student not found'
            ], 409);
        }

        $student_data_scoped->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Student deleted succesfully',
            'deleted_student' => $student_data_scoped
        ], 201);
    }

    public function update_student_scoped(Request $request, $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data_scoped = Student::where('school_branch_id', $currentSchool->id)
            ->find($student_id);
        if (!$student_data_scoped) {
            return response()->json([
                'status' => 'ok',
                'message' => 'student not found',
            ], 409);
        }

        $student_data = $request->all();
        $student_data = array_filter($student_data);
        $student_data_scoped->fill();
        $student_data_scoped->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Student data updated succesfully',
            'updated_student' => $student_data_scoped
        ], 200);
    }

    public function get_student_with_all_relations(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data_scoped = Student::where('school_branch_id', $currentSchool->id)
            ->with('parents', 'specialty')->get();
        if($student_data_scoped->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No student records found'
            ], 409);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'Students fetched succesfully',
            'student_data' => $student_data_scoped
        ], 201);
    }



}
