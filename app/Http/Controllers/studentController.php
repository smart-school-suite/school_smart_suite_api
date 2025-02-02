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
        $students = Student::where('school_branch_id', $currentSchool->id)->
                                   with(['guardianOne', 'guardianTwo',
                                    'specialty', 'level', 'studentBatch'])
                                    ->get();
        $result = [];
        foreach ($students as $student) {
            $result[] = [
                'id' => $student->id,
                'student_name' => $student->name,
                'phone_one' => $student->phone_one,
                'gender' => $student->gender,
                'specailty_name' => $student->specialty->specialty_name,
                'level_name' => $student->level->name,
                'level_number' => $student->level->level,
                'guardian_name' => $student->guardianOne->name,
                'student_batch' => $student->studentBatch->name,
            ];
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'student records fetched sucessfully',
            'students' => $result
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


        if ($student_data_scoped->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No student records found',
                'student_data' => $student_data_scoped
            ], 409);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'Students fetched succesfully',
            'student_data' => $student_data_scoped
        ], 201);
    }

    public function student_details(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_id  = $request->route('student_id');

        $find_student = Student::find($student_id);
        if (!$find_student) {
            return response()->json([
                "status" => "error",
                "message" => "Student Not Found"
            ], 400);
        }
        $student_details = Student::where("school_branch_id", $currentSchool->id)
            ->where("id", $student_id)
            ->with(['guardianOne', 'guardianTwo', 'specialty', 'level', 'studentBatch', 'department'])->get();

        return response()->json([
            "status" => "ok",
            "message" => "Student Details fetched succefully",
            "student_details" => $student_details
        ], 201);
    }
}
