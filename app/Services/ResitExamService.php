<?php

namespace App\Services;

use App\Jobs\DataCreationJob\CreateResitCandidateJob;
use App\Models\ResitExam;
use App\Jobs\CreateResitCandidates;
use App\Models\SchoolGradesConfig;
use Illuminate\Support\Facades\DB;
use Exception;

class ResitExamService
{
    public function updateResitExam($updateData, string $resitExamId, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $resit = ResitExam::where("school_branch_id", $currentSchool->id)
                ->find($resitExamId);

            if (!$resit) {
                return ApiResponseService::error("Resit Not found", null, 404);
            }

            $resit->update($updateData);
            dispatch(new CreateResitCandidateJob($resit));
            DB::commit();
            return $resit;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkUpdateResitExam($examUpdateList){
        $result = [];
        try{
           DB::beginTransaction();
           foreach($examUpdateList as $examUpdate){
                $resitExam = ResitExam::findOrFail($examUpdate['resit_exam_id']);
                $filterData = array_filter($examUpdate);
                $resitExam->update($filterData);
                $result[] = [
                     $resitExam
                ];
           }
           DB::commit();
           return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
    public function getAllResitExamsBySchoolBranch(object $currentSchool)
    {
        $resitExams = ResitExam::Where("school_branch_id", $currentSchool->id)
            ->with(['specialty', 'level', 'examType', 'semester'])
            ->get();
        return $resitExams;
    }
    public function addExamGrading(string $resitExamId, $currentSchool, $gradesConfigId)
    {
        $gradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($gradesConfigId);
        if (!$gradesConfig) {
            return ApiResponseService::error("Exam Grades Configuration Not Found", null, 404);
        }
        $exam = ResitExam::where("school_branch_id", $currentSchool->id)->find($resitExamId);
        if (!$exam) {
            return ApiResponseService::error("Exam Not Found", null, 404);
        }
        $exam->grades_category_id = $gradesConfig->grades_category_id;
        $exam->grading_added = true;
        $exam->save();
        return $exam;
    }
    public function examDetails($currentSchool, string $resitExamId)
    {
        $exam = ResitExam::where("school_branch_id", $currentSchool->id)
            ->with(['examtype', 'semester', 'specialty', 'level',])
            ->find($resitExamId);
        if (!$exam) {
            return ApiResponseService::error("Exam not found", null, 404);
        }
        return $exam;
    }
    public function deleteResitExam($resitExamId){
        $resitExam = ResitExam::findOrFail($resitExamId);
        $resitExam->delete();
        return $resitExam;
    }
    public function bulkDeleteResitExam($resitExamIds){
        $result = [ ];
        try{
            DB::beginTransaction();
            foreach($resitExamIds as $resitExamId){
                $resitExam = ResitExam::findOrFail($resitExamId['resit_exam_id']);
                $result[] = $resitExam;
            }
            DB::commit();
            return $result;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    public function bulkAddExamGrading($examGradingList, $currentSchool){
        $result = [];
        try{
           DB::beginTransaction();
           foreach($examGradingList as $examGrading){
               $gradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($examGradingList['grades_config_Id']);
               if(!$gradesConfig){
                   return ApiResponseService::error("Exam Grades Configuration Not Found", null, 404);
               }
               $exam = ResitExam::where("school_branch_id", $currentSchool->id)->find($examGrading['resit_exam_id']);
               if(!$exam){
                   return ApiResponseService::error("Exam Not Found", null, 404);
               }
               $exam->grades_category_id = $gradesConfig->grades_category_id;
               $exam->grading_added = true;
               $exam->save();
               $result[] = [
                    $gradesConfig,
                    $exam,
               ];
            }
           DB::commit();
           return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
   }
}
