<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Services\StudentService;
use App\Models\Student;
use App\Services\ApiResponseService;

class studentController extends Controller
{
    //
    //create Resource
    protected StudentService $studentService;
    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }
    public function get_all_students_in_school(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudents = $this->studentService->getStudents($currentSchool);
        return ApiResponseService::success("Student Fetched Succefully", $getStudents, null, 200);
    }

    public function delete_Student_Scoped(Request $request, $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudent = $this->studentService->deleteStudent($student_id, $currentSchool);
        return ApiResponseService::success("Student Deleted Successfully", $deleteStudent, null, 200);
    }

    public function update_student_scoped(Request $request, $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudent = $this->studentService->updateStudent($student_id, $currentSchool, $request->all());
        return ApiResponseService::success('Student Updated Successfully', $updateStudent, null, 200);
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
        $studentDetails = $this->studentService->studentDetails($student_id, $currentSchool);
        return ApiResponseService::success("Student Details Fetched Successfully", $studentDetails, null, 200);
    }
}
