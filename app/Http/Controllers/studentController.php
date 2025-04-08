<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use App\Services\StudentService;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentDropOutResource;
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
        return ApiResponseService::success("Student Details Fetched Successfully", $studentDetails, null, 200);
    }

    public function deactivateAccount(Request $request, string $studentId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deactivateAccount = $this->studentService->deactivateStudentAccount($studentId, $currentSchool);
        return ApiResponseService::success("Student Account Deactivated Succesfully", $deactivateAccount, null, 200);
    }

    public function activateAccount(Request $request, string $studentId){
        $currentSchool = $request->attributes->get('currentSchool');
        $activateAccount = $this->studentService->activateStudentAccount($studentId, $currentSchool);
        return ApiResponseService::success("Student Account Activated Sucessfully", $activateAccount, null, 200);
    }

    public function markStudentAsDropout(Request $request, string $studentId){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'reason' => 'sometimes|nullable|string'
        ]);
        $markStudentAsDropout = $this->studentService->markStudentAsDropout($studentId, $currentSchool, $request->reason);
        return ApiResponseService::success("Student Marked As Dropout Successfully", $markStudentAsDropout, null, 200);
    }
    public function getStudentDropoutDetails(Request $request, string $studentDropoutId){
        $currentSchool = $request->attributes->get('currentSchool');
        $studentDropoutDetails = $this->studentService->getDropoutStudentDetails( $studentDropoutId, $currentSchool);
        return ApiResponseService::success("Student Dropout Details Fetched Successfully", $studentDropoutDetails, null, 200);
    }
    public function getStudentDropoutList(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $studentDropoutList = $this->studentService->getAllDropoutStudents($currentSchool);
        return ApiResponseService::success("Student Dropout List Fetched Successfully", StudentDropOutResource::collection($studentDropoutList), null, 200);
    }
    public function deleteStudentDropout(Request $request, string $studentDropoutId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudentDropout = $this->studentService->deleteDropoutStudent( $studentDropoutId, $currentSchool);
        return ApiResponseService::success("Student Dropout Deleted Successfully", $deleteStudentDropout, null, 200);
    }

    public function reinstateDropedOutStudent(Request $request, string $studentDropoutId){
        $currentSchool = $request->attributes->get('currentSchool');
        $reinstateDropedOutStudent = $this->studentService->reinstateDropoutStudent($studentDropoutId, $currentSchool);
        return ApiResponseService::success("Student Reinstated Successfully", $reinstateDropedOutStudent, null, 200);
    }


}
