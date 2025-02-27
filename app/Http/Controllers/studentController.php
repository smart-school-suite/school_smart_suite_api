<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use App\Services\StudentService;
use App\Http\Requests\UpdateStudentRequest;
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
    public function getStudents(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudents = $this->studentService->getStudents($currentSchool);
        return ApiResponseService::success("Student Fetched Succefully", StudentResource::collection($getStudents), null, 200);
    }

    public function deleteStudent(Request $request, $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudent = $this->studentService->deleteStudent($student_id, $currentSchool);
        return ApiResponseService::success("Student Deleted Successfully", $deleteStudent, null, 200);
    }

    public function updateStudent(UpdateStudentRequest $request, $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudent = $this->studentService->updateStudent($student_id, $currentSchool, $request->all());
        return ApiResponseService::success('Student Updated Successfully', $updateStudent, null, 200);
    }


    public function getStudentDetails(Request $request, string $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $studentDetails = $this->studentService->studentDetails($student_id, $currentSchool);
        return ApiResponseService::success("Student Details Fetched Successfully", StudentResource::collection($studentDetails), null, 200);
    }
}
