<?php

namespace App\Services;
use App\Models\Student;
class StudentService
{
    // Implement your logic here
    public function getStudents($currentSchool)
    {
        $students = Student::where('school_branch_id', $currentSchool->id)->
            with([
                'guardianOne',
                'guardianTwo',
                'specialty',
                'level',
                'studentBatch'
            ])
            ->get();
        return $students;
    }

    public function deleteStudent($studentId, $currentSchool)
    {
        $studentExists = Student::Where("school_branch_id", $currentSchool->id)->find($studentId);
        if (!$studentExists) {
            return ApiResponseService::error("Student Not found", null, 404);
        }
        $studentExists->delete();
        return $studentExists;
    }

    public function updateStudent($studentId, $currentSchool, array $data)
    {
        $studentExists = Student::Where("school_branch_id", $currentSchool->id)->find($studentId);
        if (!$studentExists) {
            return ApiResponseService::error("Student Not found", null, 404);
        }

        $filteredData = array_filter($data);
        $studentExists->update($filteredData);
        return $studentExists;
    }

    public function studentDetails($studentId, $currentSchool)
    {
        $studentDetails = Student::where("school_branch_id", $currentSchool->id)
            ->with(['guardianOne', 'guardianTwo', 'specialty', 'level', 'studentBatch', 'department'])
            ->find($studentId);
        return $studentDetails;
    }
}
