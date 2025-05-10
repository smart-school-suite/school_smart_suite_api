<?php

namespace App\Services;

use App\Models\Exams;
use Illuminate\Support\Str;
use App\Models\LetterGrade;
use App\Models\SchoolGradesConfig;
use App\Jobs\CreateExamCandidatesJob;
use App\Models\Examtype;
use App\Models\Student;
use App\Models\Specialty;
use Exception;
use Illuminate\Support\Facades\DB;

class ExamService
{
    // Implement your logic here

    public function createExam(array $data, $currentSchool)
    {
        $specialty = Specialty::findOrFail($data['specialty_id']);
        $examType = Examtype::findOrFail($data['exam_type_id']);
        $examId = Str::uuid();
        $exam = new Exams();
        $exam->id = $examId;
        $exam->school_branch_id = $currentSchool->id;
        $exam->start_date = $data["start_date"];
        $exam->end_date = $data["end_date"];
        $exam->level_id = $specialty->level_id;
        $exam->exam_type_id = $examType->id;
        $exam->weighted_mark = $data["weighted_mark"];
        $exam->semester_id = $examType->semester_id;
        $exam->school_year = $data["school_year"];
        $exam->specialty_id = $specialty->id;
        $exam->student_batch_id = $data["student_batch_id"];
        $exam->save();
        CreateExamCandidatesJob::dispatch($data['specialty_id'], $specialty->level_id, $data['student_batch_id'], $examId);
        return $exam;

    }
    public function deleteExam(string $exam_id, $currentSchool)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->find($exam_id);
        if (!$exam) {
            return ApiResponseService::error("Exam not found", null, 404);
        }

        $exam->delete();
        return $exam;
    }
    public function bulkDeleteExam($examIds){
         $result = [];
         try{
            DB::beginTransaction();
           foreach($examIds as $examId){
              $exam = Exams::find($examId);
              $exam->delete();
              $result[] = [
                 $result
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
    public function updateExam(string $exam_id, $currentSchool, array $data)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->find($exam_id);
        if (!$exam) {
            return ApiResponseService::error("Exam not found", null, 404);
        }

        $filterData = array_filter($data);
        $exam->update($filterData);
        return $exam;
    }
    public function bulkUpdateExam($examUpdateList){
        $result = [];
        try{
           DB::beginTransaction();
           foreach($examUpdateList as $examUpdate){
                $exam = Exams::findOrFail($examUpdate['exam_id']);
                $filterData = array_filter($examUpdate);
                $exam->update($filterData);
                $result[] = [
                     $exam
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
    public function getExams($currentSchool)
    {
        $exams = Exams::where('school_branch_id', $currentSchool->id)
            ->with(['examtype', 'semester', 'specialty', 'level', 'studentBatch'])
            ->get();
        return $exams;
    }
    public function examDetails($currentSchool, string $exam_id)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->with(['examtype', 'semester', 'specialty', 'level', 'studentBatch'])
            ->find($exam_id);
        if (!$exam) {
            return ApiResponseService::error("Exam not found", null, 404);
        }
        return $exam;
    }
    public function getAccessExams(string $student_id, $currentSchool)
    {
        $findStudent = Student::where("school_branch_id", $currentSchool->id)->find($student_id);
        if (!$findStudent) {
            return ApiResponseService::error("Student Not Found", null, 404);
        }
        $examData = Exams::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $findStudent->specialty_id)
            ->where("level_id", $findStudent->level_id)
            ->with(["examtype"])
            ->get();
        return $examData;
    }
    public function getAssociateWeightedMarkLetterGrades(string $exam_id, $currentSchool)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->with(["examtype"])->find($exam_id);
        if (!$exam) {
            return ApiResponseService::error("Exam Data not found", null, 404);
        }
        $letterGrades = LetterGrade::all();
        return $letterGrades && $exam;
    }
    public function addExamGrading(string $examId, $currentSchool, $gradesConfigId){
        $gradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($gradesConfigId);
        if(!$gradesConfig){
            return ApiResponseService::error("Exam Grades Configuration Not Found", null, 404);
        }
        $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examId);
        if(!$exam){
            return ApiResponseService::error("Exam Not Found", null, 404);
        }
        $exam->grades_category_id = $gradesConfig->grades_category_id;
        $exam->grading_added = true;
        $exam->save();
        return $exam;
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
                $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examGrading['exam_id']);
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
    public function getResitExams($currentSchool){
        $exams = Exams::where("school_branch_id", $currentSchool->id)
            ->whereHas('examType', function($query) {
            $query->where('type', 'resit');
        })->with(['examtype', 'semester', 'specialty', 'level', 'studentBatch'])->get();
        return $exams;
    }


}
