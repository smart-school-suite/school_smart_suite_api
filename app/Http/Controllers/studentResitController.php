<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StudentResitService;
use App\Services\ApiResponseService;

class studentResitController extends Controller
{
    //
    protected StudentResitService $studentResitService;
    public function __construct(StudentResitService $studentResitService)
    {
        $this->studentResitService = $studentResitService;
    }
    public function update_student_resit(Request $request, $resit_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudentResit = $this->studentResitService->updateStudentResit($request->all(), $currentSchool, $resit_id);
        return ApiResponseService::success("Resit Entry Updated Successfully", $updateStudentResit, null, 200);
    }

    public function pay_for_resit(Request $request, $resit_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payStudentResit = $this->studentResitService->payResit($currentSchool, $resit_id);
        return ApiResponseService::success("Student Resit Paid Successfully", $payStudentResit, null, 200);
    }

    public function delete_student_resit_record(Request $request, $resit_id)
    {
        $resit_id = $request->route('resit_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudentResit = $this->studentResitService->deleteStudentResit($resit_id, $currentSchool);
        return ApiResponseService::success("Student Resit Record Not Found", $deleteStudentResit, null, 200);
    }

    public function get_my_resits(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_id = $request->route('student_id');
        $exam_id = $request->route("exam_id");
        $getMyResits = $this->studentResitService->getMyResits($currentSchool, $student_id, $exam_id);
        return ApiResponseService::success("Student Records Fetched Sucessfully", $getMyResits, null, 200);
    }

    public function get_student_resits(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentResits = $this->studentResitService->getStudentResits($currentSchool);
        return ApiResponseService::success("Student Resit Records Fetched Sucessfully", $getStudentResits, null, 200);
    }

    public function student_resit_details(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $resit_id = $request->route("resit_id");
        $getStudentResitDetails = $this->studentResitService->getStudentResitDetails($currentSchool, $resit_id);
        return ApiResponseService::success("Student Resit Details Fetched Successfully", $getStudentResitDetails, null, 200);
    }
}
