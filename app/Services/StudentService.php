<?php

namespace App\Services;
use App\Models\Student;
use App\Models\StudentDropout;

class StudentService
{
    // Implement your logic here
    public function getStudents($currentSchool)
    {
        $students = Student::where('school_branch_id', $currentSchool->id)->
            with([
                'guardian',
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
            ->with([
                'guardian',
                'specialty',
                'level',
                'studentBatch'
            ])
            ->find($studentId);
        return $studentDetails;
    }

    public function deactivateStudentAccount($studentId, $currentSchool){
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $student->account_status = 'inactive';
        $student->save();
        return $student;
    }

    public function activateStudentAccount($studentId, $currentSchool){
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $student->account_status = 'active';
        $student->save();
        return $student;
    }

    public function markStudentAsDropout($studentId, $currentSchool, $reason)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $studentDropout = StudentDropout::where('student_id', $student->id)->first();
        if ($studentDropout) {
            return ApiResponseService::error("Student already marked as dropout", null, 400);
        }
        StudentDropout::create([
            'student_id' => $student->id,
            'level_id' => $student->level_id,
            'reason' => $reason,
            'specialty_id' => $student->specialty_id,
            'school_branch_id' => $student->school_branch_id,
            'student_batch_id' => $student->student_batch_id,
            'department_id' => $student->department_id
        ]);

        return $student;
    }

    public function getAllDropoutStudents($currentSchool)
    {
        $dropoutStudents = StudentDropout::where('school_branch_id', $currentSchool->id)
            ->with([
                'student',
                'level',
                'specialty',
                'studentBatch',
                'department'
            ])
            ->get();
        return $dropoutStudents;
    }
    public function getDropoutStudentDetails(string $studentDropoutId, $currentSchool)
    {
        $dropoutStudentDetails = StudentDropout::where('school_branch_id', $currentSchool->id)
            ->with([
                'student',
                'level',
                'specialty',
                'studentBatch'
            ])
            ->find( $studentDropoutId);
        return $dropoutStudentDetails;
    }
    public function deleteDropoutStudent(string $studentDropoutId, $currentSchool)
    {
        $dropoutStudent = StudentDropout::where('school_branch_id', $currentSchool->id)->find( $studentDropoutId);
        if (!$dropoutStudent) {
            return ApiResponseService::error("Dropout Student Not found", null, 404);
        }
        $dropoutStudent->delete();
        return $dropoutStudent;
    }

    public function reinstateDropoutStudent(string $studentDropoutId, $currentSchool)
    {
        $dropoutStudent = StudentDropout::where('school_branch_id', $currentSchool->id)->find($studentDropoutId);
        if (!$dropoutStudent) {
            return ApiResponseService::error("Dropout Student Not found", null, 404);
        }
        $dropoutStudent->delete();
        return $dropoutStudent;
    }
}
