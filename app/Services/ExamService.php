<?php

namespace App\Services;

use App\Models\Exams;
use App\Models\LetterGrade;
use App\Models\Student;

class ExamService
{
    // Implement your logic here

    public function createExam(array $data, $currentSchool)
    {
        $new_examdata_instance = new Exams();
        $new_examdata_instance->school_branch_id = $currentSchool->id;
        $new_examdata_instance->start_date = $data["start_date"];
        $new_examdata_instance->end_date = $data["end_date"];
        $new_examdata_instance->level_id = $data["level_id"];
        $new_examdata_instance->exam_type_id = $data["exam_type_id"];
        $new_examdata_instance->weighted_mark = $data["weighted_mark"];
        $new_examdata_instance->semester_id = $data["semester_id"];
        $new_examdata_instance->school_year = $data["school_year"];
        $new_examdata_instance->specialty_id = $data["specialty_id"];
        $new_examdata_instance->save();
        return $new_examdata_instance;
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

    public function getExams($currentSchool)
    {
        $exams = Exams::where('school_branch_id', $currentSchool->id)
            ->with(['examtype', 'semester', 'specialty', 'level'])
            ->get();
        return $exams;
    }

    public function examDetails($currentSchool, string $exam_id)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->with(['specialty', 'examtype', 'semester', 'level',])
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
}
