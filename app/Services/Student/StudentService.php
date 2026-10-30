<?php

namespace App\Services\Student;

use App\Models\Student;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AppException;
use App\Services\ApiResponseService;

class StudentService
{
    public function getStudents(object $currentSchool)
    {
        $students = Student::where('school_branch_id', $currentSchool->id)->with([
            'guardian',
            'specialty.level',
            'specialty.department',
            'studentBatch',
            'gender',
            'relationship',
            'currentAvatar'
        ])
            ->where("dropout_status", false)->get();
        return $students;
    }
    public function deleteStudent(string $studentId, object $currentSchool, object $authAdmin)
    {
        $studentExists = Student::Where("school_branch_id", $currentSchool->id)->find($studentId);
        if (!$studentExists) {
            return ApiResponseService::error("Student Not found", null, 404);
        }
        $studentExists->delete();
        return $studentExists;
    }
    public function updateStudent(string $studentId, object $currentSchool, array $data, $authAdmin)
    {
        $studentExists = Student::Where("school_branch_id", $currentSchool->id)->find($studentId);
        if (!$studentExists) {
            return ApiResponseService::error("Student Not found", null, 404);
        }

        $filteredData = array_filter($data);
        $studentExists->update($filteredData);
        return $studentExists;
    }
    public function studentDetails(string $studentId, object $currentSchool)
    {
        $studentDetails = Student::where("school_branch_id", $currentSchool->id)
            ->with([
                'guardian',
                'specialty.level',
                'gender',
                'studentSource',
                'studentBatch',
                'relationship'
            ])
            ->find($studentId);
        return $studentDetails;
    }
    public function deactivateStudentAccount(string $studentId, object $currentSchool, $authAdmin)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $student->status = 'inactive';
        $student->save();
        return $student;
    }
    public function activateStudentAccount(string $studentId, object $currentSchool, $authAdmin)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $student->status = 'active';
        $student->save();
        return $student;
    }
    public function markStudentAsDropout(string $studentId, object $currentSchool, $reason, $authAdmin)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);

        $student->dropout_status = true;
        $student->save();
        return $student;
    }
    public function bulkReinstateDropOutStudent(array $studentDropoutList, object $currentSchool, $authAdmin)
    {
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($studentDropoutList as $studentDropout) {
                $student = Student::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($studentDropout['student_id']);
                $student->dropout_status = false;
                $student->save();
                $studentIds[] = $student->id;
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getAllDropoutStudents(object $currentSchool)
    {
        try {
            $dropoutStudents = Student::where('school_branch_id', $currentSchool->id)
                ->with([
                    'guardian',
                    'specialty.level',
                    'specialty.department',
                    'studentBatch',
                    'gender',
                    'relationship'
                ])
                ->where('dropout_status', true)
                ->get();

            return $dropoutStudents;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving dropout students.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of dropout students from being retrieved successfully.",
                null
            );
        }
    }
    public function reinstateDropoutStudent(string $studentDropoutId, object $currentSchool, $authAdmin)
    {
        $dropoutStudent = Student::where('school_branch_id', $currentSchool->id)->find($studentDropoutId);
        if (!$dropoutStudent) {
            return ApiResponseService::error("Dropout Student Not found", null, 404);
        }
        $dropoutStudent->dropout_status = false;
        $dropoutStudent->save();
        return $dropoutStudent;
    }
    public function bulkMarkStudentAsDropOut(array $studentDropdoutList, object $currentSchool, $authAdmin)
    {
        $result = [];
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($studentDropdoutList as $studentDropout) {
                $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentDropout['student_id']);
                $student->dropout_status = true;
                $result[] = $student;
                $studentIds = $student->id;
                $student->save();
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteStudent(array $studentIds, object $currentSchool, $authAdmin)
    {
        $result = [];
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($studentIds as $studentId) {
                $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId['student_id']);
                $student->delete();
                $result[] = $student;
                $studentIds[] = $student->id;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkUpdateStudent(array $updateData, object $currentSchool, $authAdmin)
    {
        $result = [];
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($updateData as $data) {
                $student = Student::where("school_branch_id", $currentSchool->id)->find($data['student_id']);
                $filteredData = array_filter($data);
                $student->update($filteredData);
                $result[] = $student;
                $studentIds[] = $student->id;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkActivateStudent(array $studentIds, object $currentSchool, $authAdmin)
    {
        $result = [];
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($studentIds as $studentId) {
                $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId['student_id']);
                $student->status =  'active';
                $student->save();
                $result[] = $student;
                $studentIds[] = $student->id;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeactivateStudent($studentIds, $currentSchool, $authAdmin)
    {
        $result = [];
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($studentIds as $studentId) {
                $student = Student::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($studentId['student_id']);
                $student->status =  'inactive';
                $student->save();
                $result[] = $student;
                $studentIds[] = $studentIds;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkReinstateStudent($dropOutIds, $currentSchool, $authAdmin)
    {
        $result = [];
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($dropOutIds as $dropOutId) {
                $studentDropout = Student::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($dropOutId['student_id']);
                $studentDropout->dropout_status = false;
                $studentDropout->save();
                $result[] = $studentDropout;
                $studentIds[] = $studentDropout->id;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getStudentProfileDetails(object $currentSchool, string $studentId)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)
            ->with(['department', 'specialty.level', 'guardian', 'level'])
            ->find($studentId);
        return $student;
    }
}
