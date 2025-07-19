<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class StudentService
{
    // Implement your logic here
    public function getStudents($currentSchool)
    {
        $students = Student::where('school_branch_id', $currentSchool->id)->with([
            'guardian',
            'specialty',
            'level',
            'studentBatch'
        ])
          ->where("dropout_status", false)  ->get();
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
    public function deactivateStudentAccount($studentId, $currentSchool)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $student->status = 'inactive';
        $student->save();
        return $student;
    }
    public function activateStudentAccount($studentId, $currentSchool)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $student->status = 'active';
        $student->save();
        return $student;
    }
    public function markStudentAsDropout($studentId, $currentSchool, $reason)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);

        $student->dropout_status = true;
        $student->save();
        return $student;
    }
    public function getAllDropoutStudents($currentSchool)
    {
        $dropoutStudents = Student::where('school_branch_id', $currentSchool->id)
            ->with([
                'level',
                'specialty',
                'studentBatch',
                'department'
            ])
            ->where('dropout_status', true)
            ->get();
        return $dropoutStudents;
    }
    public function reinstateDropoutStudent(string $studentDropoutId, $currentSchool)
    {
        $dropoutStudent = Student::where('school_branch_id', $currentSchool->id)->find($studentDropoutId);
        if (!$dropoutStudent) {
            return ApiResponseService::error("Dropout Student Not found", null, 404);
        }
        $dropoutStudent->dropout_status = false;
        $dropoutStudent->save();
        return $dropoutStudent;
    }
    public function bulkMarkStudentAsDropOut($studentDropdoutList, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentDropdoutList as $studentDropout) {
                $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentDropout['student_id']);
                $student->dropout_status = true;
                $student->save();
                $result[] = $student;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteStudent($studentIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentIds as $studentId) {
                $student = Student::findOrFail($studentId['student_id']);
                $student->delete();
                $result[] = $student;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkUpdateStudent($updateData)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateData as $data) {
                $student = Student::find($data['student_id']);
                $filteredData = array_filter($data);
                $student->update($filteredData);
                $result[] = $student;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkActivateStudent($studentIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentIds as $studentId) {
                $student = Student::findOrFail($studentId['student_id']);
                $student->status =  'active';
                $student->save();
                $result[] = $student;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeactivateStudent($studentIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentIds as $studentId) {
                $student = Student::findOrFail($studentId['student_id']);
                $student->status =  'inactive';
                $student->save();
                $result[] = $student;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkReinstateStudent($dropOutIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($dropOutIds as $dropOutId) {
                $studentDropout = Student::findOrFail($dropOutId['student_id']);
                $studentDropout->dropout_status = false;
                $studentDropout->save();
                $result[] = $studentDropout;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function uploadProfilePicture($request, $authStudent)
    {
        $student = Student::find($authStudent->id);
        if (!$student) {
            return ApiResponseService::error("Student Not Found", null, 400);
        }
        try {
            DB::transaction(function () use ($request, $student) {

                if ($student->profile_picture) {
                    Storage::disk('public')->delete('StudentAvatars/' . $student->profile_picture);
                }
                $profilePicture = $request->file('profile_picture');
                $fileName = time() . '.' . $profilePicture->getClientOriginalExtension();
                $profilePicture->storeAs('public/StudentAvatars', $fileName);

                $student->profile_picture = $fileName;
                $student->save();
            });
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function deleteProfilePicture($authStudent)
    {
        try {
            $student = Student::find($authStudent->id);
            if (!$student) {
                return ApiResponseService::error("Student Not found", null, 400);
            }
            if (!$student->profile_picture) {
                return ApiResponseService::error("No Profile Picture to Delete {$student->name}", null, 400);
            }
            Storage::disk('public')->delete('SchoolAdminAvatars/' . $student->profile_picture);

            $student->profile_picture = null;
            $student->save();

            return $student;
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
