<?php

namespace App\Services;

use App\Jobs\DataCreationJob\CreateExamCandidateJob;
use App\Jobs\NotificationJobs\SendAdminExamCreatedNotificationJob;
use App\Jobs\NotificationJobs\SendExamTimetableAvailableNotification;
use App\Models\Exams;
use Illuminate\Support\Str;
use App\Models\LetterGrade;
use App\Models\SchoolGradesConfig;
use App\Models\AccessedStudent;
use App\Models\Examtype;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Specialty;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamService
{
    // Implement your logic here

    public function createExam(array $data, $currentSchool)
    {
        $specialty = Specialty::with(['level'])->findOrFail($data['specialty_id']);
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
        $examData =  [
            'specialty' => $specialty->specialty_name,
            'level' => $specialty->level->name,
            'startDate' => Carbon::parse($data['start_date'])->format('l, F j, Y'),
            'endDate' => Carbon::parse($data['end_date'])->format('l, F j, Y'),
            'school_year' => $data['school_year'],
            'semester' => Semester::find($examType->semester_id)->name,
            'examName' => $examType->exam_name
        ];
        CreateExamCandidateJob::dispatch(
            $data['specialty_id'],
             $specialty->level_id,
              $data['student_batch_id'],
               $examId
        );
        SendAdminExamCreatedNotificationJob::dispatch($currentSchool->id,
             $examData
         );
        return $exam;

    }
    public function deleteExam(string $examId, $currentSchool)
    {
        $this->deleteExamCandidates($examId);
        $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examId);
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
              $this->deleteExamCandidates($examId['exam_id']);
              $exam = Exams::find($examId['exam_id']);
              $exam->delete();
              $result[] = $exam;
           }
           DB::commit();
           return $result;
         }
         catch(Exception $e){
            DB::rollBack();
            throw $e;
         }
    }
    public function updateExam(string $examId, $currentSchool, array $data)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examId);
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
    public function examDetails($currentSchool, string $examId)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->with(['examtype', 'semester', 'specialty', 'level', 'studentBatch'])
            ->find($examId);
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
    public function getAssociateWeightedMarkLetterGrades(string $examId, $currentSchool)
    {
        $results = [];
        $exam = Exams::where("school_branch_id", $currentSchool->id)->with(["examtype"])->find($examId);
        if (!$exam) {
            return ApiResponseService::error("Exam Data not found", null, 404);
        }
        $letterGrades = LetterGrade::all();
        foreach($letterGrades as $letterGrade){
            $results[] = [
                "letter_grade" => $letterGrade,
                "exam" => $exam,
            ];
        }
        return $results;
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
                $gradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)
                ->find($examGrading['grades_config_Id']);
                if(!$gradesConfig){
                    return ApiResponseService::error("Exam Grades Configuration Not Found", null, 404);
                }
                $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examGrading['exam_id']);
                if(!$exam){
                    return ApiResponseService::error("Exam Not Found", null, 404);
                }
                Log::info("exam-details", $exam->toArray());
                Log::info("gradesConfig", $gradesConfig->toArray());
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

    private function deleteExamCandidates($examId)
    {
        $examCandidates = AccessedStudent::where("exam_id", $examId)->get();
        if ($examCandidates) {
            foreach ($examCandidates as $examCandidate) {
                $examCandidate->delete();
            }
        }
    }

}
