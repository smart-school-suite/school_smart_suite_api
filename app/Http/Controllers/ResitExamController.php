<?php

namespace App\Http\Controllers;
use App\Http\Requests\ResitExam\ResitExamIdRequest;
use App\Http\Requests\ResitExam\UpdateResitExamRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\ExamGrading\BulkAddResitExamGradingRequest;
use App\Http\Requests\Exam\BulkUpdateResitExamRequest;
use App\Http\Resources\ResitExamResource;
use App\Services\ResitExamService;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;

class ResitExamController extends Controller
{
    protected ResitExamService $resitExamService;
    public function __construct(ResitExamService $resitExamService){
        $this->resitExamService = $resitExamService;
    }

    public function updateResitExam(UpdateResitExamRequest $request, $resitExamId){
        try{
          $currentSchool = $request->attributes->get('currentSchool');
          $updateResitExam = $this->resitExamService->updateResitExam($request->validated(), $resitExamId, $currentSchool);
          return ApiResponseService::success("Resit Exam Updated Successfully", $updateResitExam, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getAllResitExams(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $resitExams = $this->resitExamService->getAllResitExamsBySchoolBranch($currentSchool);
        return ApiResponseService::success("Resit Exams Fetched Successfully", ResitExamResource::collection($resitExams), null, 200);
    }

    public function deleteResitExam( $resitExamId){
        $deleteResitExam = $this->resitExamService->deleteResitExam($resitExamId);
        return ApiResponseService::success("Resit Exam Deleted Successfully", $deleteResitExam, null, 200);
    }

    public  function addResitExamGrading(Request $request){
        $resitExamId = $request->route('resitExamId');
        $gradesConfigId = $request->route('gradesConfigId');
        $currentSchool = $request->attributes->get("currentSchool");
        $addGrading = $this->resitExamService->addExamGrading($resitExamId, $currentSchool, $gradesConfigId);
        return ApiResponseService::success("Resit Exam Grading Added Successfully", $addGrading, null, 200);
    }

    public function getResitExamDetails(Request $request, $resitExamId){
        $currentSchool = $request->attributes->get("currentSchool");
        $resitExamDetails = $this->resitExamService->examDetails($currentSchool, $resitExamId);
        return ApiResponseService::success("Resit Exam Details Fetched Successfully", $resitExamDetails, null, 200);
    }

    public function bulkDeleteResitExam(ResitExamIdRequest $request){
        try{
           $deleteExam = $this->resitExamService->bulkDeleteResitExam($request->resitExamIds);
           return ApiResponseService::success("Exam Deleted Succesfully", $deleteExam, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkAddExamGrading(BulkAddResitExamGradingRequest $request) {
        try{
         $currentSchool = $request->attributes->get('currentSchool');
         $bulkAddExamGrading = $this->resitExamService->bulkAddExamGrading($request->exam_grading, $currentSchool);
         return ApiResponseService::success("Exam Grading Added Successfully", $bulkAddExamGrading, null, 200);
        }
        catch(Exception $e){
         return ApiResponseService::error($e->getMessage(), null, 400);
        }
     }

     public function bulkUpdateResitExam(BulkUpdateResitExamRequest $request){
        try{
            $bulkUpdateExam = $this->resitExamService->bulkUpdateResitExam($request->exams);
            return ApiResponseService::success("Exam Updated Successfully", $bulkUpdateExam, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
