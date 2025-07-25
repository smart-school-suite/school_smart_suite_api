<?php

namespace App\Services;
use App\Models\Grades;
use App\Models\Examtype;
use App\Models\LetterGrade;
use App\Models\Exams;
use Illuminate\Support\Facades\DB;
use Exception;
class GradesService
{
    // Implement your logic here
    public function getExamGrades($currentSchool){
        $gradesData = Grades::where('school_branch_id', $currentSchool->id)
            ->with(['exam.examtype.semesters', 'lettergrade'])->get();
            return $gradesData;
    }
    public function deleteExamGrading($currentSchool, $examId){
        $exam = Exams::where('school_branch_id', $currentSchool->id)->findOrFail($examId);
        $exam->grades_category_id = null;
        $exam->grading_added = false;
        $exam->save();
        return $exam;
    }
    public function bulkDeleteExamGrading($examIds, $currentSchool){
        $results = [];
        try{
            DB::beginTransaction();
            foreach($examIds as $examId){
                $exam = Exams::where('school_branch_id', $currentSchool->id)->findOrFail($examId);
                $exam->grades_category_id = null;
                $exam->grading_added = false;
                $exam->save();
                $results[] = [
                    'exam' => $exam
                ];
            }
            DB::commit();
            return $results;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function updateExamGrading($currentSchool, $examId, $updateData){
        $exams = Exams::where('school_branch_id', $currentSchool->id)->findOrFail($examId);
        $exams->grades_category_id = $updateData['grades_category_id'];
        $exams->grading_added = true;
        $exams->save();
    }
    public function getExamGradesConfiguration($currentSchool, string $examId){
        $exam = Exams::where('school_branch_id', $currentSchool->id)->findOrFail($examId);
        $grades = Grades::where("school_branch_id", $currentSchool->id)
        ->where("grades_category_id", $exam->grades_category_id)
          ->with(['lettergrade'])
        ->get();
        return $grades;
    }
    public function getExamConfigData($currentSchool, string $examId){
        $exam = Exams::where('school_branch_id', $currentSchool->id)
        ->with(['specialty', 'level', 'examType', 'semester'])
        ->find($examId);
        $examType = $exam->examType;
        if (!$examType || $examType->type == 'exam') {
            $semester = $examType->semester;
            $caExamType = Examtype::where('semester', $semester)
                ->where('type', 'ca')
                ->first();
                if (!$caExamType) {
                    return ApiResponseService::error("exam type not found", null, 404);
                }
            $additionalExams = Exams::where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('semester_id', $exam->semester_id)
            ->where("level_id", $exam->level_id)
            ->where("school_year", $exam->school_year)
            ->with(['examType', 'level', 'specialty', 'semester'])
            ->first();
            if(!$additionalExams){
                return ApiResponseService::error("No related exams found for {$exam->specialty->specialty_name} {$exam->level->level_name}", null, 404);
            }
            $letterGrades = LetterGrade::all();
            $examGradesData = [];
            foreach($letterGrades as $letterGrade){
                $examGradesData[] = [
                    'letter_grade_id' => $letterGrade->id,
                    'letter_grade' => $letterGrade->letter_grade,
                    'weighted_score' => ($exam->weighted_mark + $additionalExams->weighted_mark),
                    'level_id' => $exam->level_id,
                    'specailty_id' => $exam->specialty_id,
                    'exam_id' => $exam->id
                ];
            }
            return $examGradesData;
        } else {
            return ApiResponseService::error("This is not an exam", null, 400);
        }

    }
}
