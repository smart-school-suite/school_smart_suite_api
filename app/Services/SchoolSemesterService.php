<?php

namespace App\Services;

use App\Models\FeeSchedule;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolSemesterService
{
    // Implement your logic here

    public function createSchoolSemester($semesterData, $currentSchool)
    {
        DB::beginTransaction();
        $schoolSemester = new SchoolSemester();
        $schoolSemesterId = Str::uuid();
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                               ->find($semesterData['specialty_id']);
        $schoolSemester->id = $schoolSemesterId;
        $schoolSemester->start_date = $semesterData["start_date"];
        $schoolSemester->end_date = $semesterData["end_date"];
        $schoolSemester->school_year = $semesterData["school_year"];
        $schoolSemester->semester_id = $semesterData["semester_id"];
        $schoolSemester->specialty_id = $semesterData["specialty_id"];
        $schoolSemester->status = 'active';
        $schoolSemester->student_batch_id = $semesterData["student_batch_id"];
        $schoolSemester->school_branch_id = $currentSchool->id;
        $schoolSemester->save();
        FeeSchedule::create([
               'specialty_id' => $semesterData['specialty_id'],
               'level_id' => $specialty->level_id,
               'school_branch_id' => $currentSchool->id,
               'school_semester_id' => $schoolSemesterId
        ]);
        DB::commit();
        return $schoolSemester;
    }

    public function updateSchoolSemester($semesterData, $currentSchool, $schoolSemesterId)
    {
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)->find($schoolSemesterId);
        if (!$schoolSemester) {
            return ApiResponseService::error("School Semester Not Found", null, 404);
        }

        $filteredData = array_filter($semesterData);
        $schoolSemester->update($filteredData);
        return $schoolSemester;
    }

    public function bulkUpdateSchoolSemester(array $updateSemesterList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateSemesterList as $updateSemester) {
                $schoolSemester = SchoolSemester::findOrFail($updateSemester['semester_id']);
                $filteredData = array_filter($updateSemester);
                $schoolSemester->update($filteredData);
                $result[] = [
                    $schoolSemester
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function deleteSchoolSemester($schoolSemesterId, $currentSchool)
    {
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)->find($schoolSemesterId);
        if (!$schoolSemester) {
            return ApiResponseService::error("School Semester Not Found", null, 404);
        }
        $schoolSemester->delete();
        return $schoolSemester;
    }

    public function bulkDeleteSchoolSemester($schoolSemesterIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($schoolSemesterIds as $schoolSemesterId) {
                $schoolSemester = SchoolSemester::findOrFail($schoolSemesterId['school_semester_id']);
                $schoolSemester->delete();
                $result[] = $schoolSemester;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSchoolSemesters($currentSchool)
    {
        $schoolSemesters = SchoolSemester::with(['specailty', 'specailty.level', 'semester', 'studentBatch'])->where("school_branch_id", $currentSchool->id)->get();
        return $schoolSemesters;
    }

    public function getActiveSchoolSemesters($currentSchool)
    {
        $schoolSemesters = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specailty', 'specailty.level', 'semester', 'studentBatch'])
            ->where("status", "active")
            ->get();
        return $schoolSemesters;
    }

    public function getSchoolSemesterDetail($currentSchool, $semesterId)
    {
        $schoolSemesterDetails = SchoolSemester::with(['specailty', 'specailty.level', 'semester', 'studentBatch'])->where("school_branch_id", $currentSchool->id)->find($semesterId);
        return $schoolSemesterDetails;
    }
}
