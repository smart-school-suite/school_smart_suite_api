<?php

namespace App\Services;

use App\Models\Examtype;

class ExamTypeService
{
    // Implement your logic here
    public function createExamType(array $data)
    {
        $new_exam_type_instance = new ExamType();
        $new_exam_type_instance->semester_id = $data["semester_id"];
        $new_exam_type_instance->exam_name = $data["exam_name"];
        $new_exam_type_instance->program_name = $data["program_name"];
        $new_exam_type_instance->save();
        return $new_exam_type_instance;
    }

    public function updateExamType(array $data, $exam_type_id)
    {
        $findExam = Examtype::find($exam_type_id);
        if (!$findExam) {
            return ApiResponseService::error("Exam type not found", null, 404);
        }
        $filterData = array_filter($data);
        $findExam->update($filterData);
        return $findExam;
    }

    public function deleteExamType($exam_type_id)
    {
        $findExam = Examtype::find($exam_type_id);
        if (!$findExam) {
            return ApiResponseService::error("Exam Type not found", null, 404);
        }
    }

    public function getExamType($currentSchool)
    {
        $num_semesters = $currentSchool->semester_count;
        $exam_type_data = Examtype::with('semesters')
            ->whereHas('semesters', function ($query) use ($num_semesters) {
                $query->whereBetween('count', [1, $num_semesters + 1]);
            })->get();
        if ($exam_type_data->isEmpty()) {
            return ApiResponseService::error('No exam types found within the specified semester count range.', null, 404);
        }
        return $exam_type_data;
    }
}
