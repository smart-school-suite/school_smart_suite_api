<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use Exception;
use Illuminate\Http\Request;
use App\Services\StudentService;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Requests\Student\BulkAddStudentDropoutRequest;
use App\Http\Requests\Student\BulkUpdateStudentRequest;
use App\Http\Resources\StudentDropOutResource;
use Illuminate\Support\Facades\Validator;
use App\Services\ApiResponseService;

class StudentController extends Controller
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
    public function getStudentDropoutList(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $studentDropoutList = $this->studentService->getAllDropoutStudents($currentSchool);
        return ApiResponseService::success("Student Dropout List Fetched Successfully", StudentDropOutResource::collection($studentDropoutList), null, 200);
    }

    public function reinstateDropedOutStudent(Request $request, string $studentDropoutId){
        $currentSchool = $request->attributes->get('currentSchool');
        $reinstateDropedOutStudent = $this->studentService->reinstateDropoutStudent($studentDropoutId, $currentSchool);
        return ApiResponseService::success("Student Reinstated Successfully", $reinstateDropedOutStudent, null, 200);
    }

    public function bulkMarkStudentAsDropout(BulkAddStudentDropoutRequest $request){
         try{
            $currentSchool = $request->attributes->get('currentSchool');
            $bulkMarkStudentAsDropout = $this->studentService->bulkMarkStudentAsDropOut($request->dropout_list, $currentSchool);
            return ApiResponseService::success("Student Marked As Dropout Succesfully", $bulkMarkStudentAsDropout, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }

    public function bulkDeleteStudent($studentIds){
        $idsArray = explode(',', $studentIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
           $bulkDeleteStudent = $this->studentService->bulkDeleteStudent($idsArray);
          return ApiResponseService::success("Student Deleted Successfully", $bulkDeleteStudent, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkUpdateStudent(BulkUpdateStudentRequest $request){
        try{
           $bulkUpdateStudent = $this->studentService->bulkUpdateStudent($request->students);
           ApiResponseService::success("Students Updated Succesfully", $bulkUpdateStudent, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkActivateStudent($studentIds){
        $idsArray = explode(',', $studentIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
           $bulkActivateStudent = $this->studentService->bulkActivateStudent($idsArray);
           return ApiResponseService::success("Student Activated Succesfully", $bulkActivateStudent, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeactivateStudent($studentIds){
        $idsArray = explode(',', $studentIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
          $bulkDeactivateStudent = $this->studentService->bulkDeactivateStudent($idsArray);
          return ApiResponseService::success("Student Deactivated Successfully", $bulkDeactivateStudent, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkReinstateStudentDropout($dropOutIds){
        $idsArray = explode(',', $dropOutIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student_dropout,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
          $bulkReinstateStudent = $this->studentService->bulkReinstateStudent($dropOutIds);
          return ApiResponseService::success("Student Reinstated Succesfully", $bulkReinstateStudent, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
