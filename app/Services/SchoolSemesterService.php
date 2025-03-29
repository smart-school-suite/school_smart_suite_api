<?php

namespace App\Services;

use App\Models\SchoolSemester;

class SchoolSemesterService
{
    // Implement your logic here

    public function createSchoolSemester($semesterData, $currentSchool){
        $schoolSemester = new SchoolSemester();
        $schoolSemester->start_date = $semesterData["start_date"];
        $schoolSemester->end_date = $semesterData["end_date"];
        $schoolSemester->school_year_start = $semesterData["school_year_start"];
        $schoolSemester->school_year_end = $semesterData["school_year_end"];
        $schoolSemester->semester_id = $semesterData["semester_id"];
        $schoolSemester->specialty_id = $semesterData["specialty_id"];
        $schoolSemester->student_batch_id = $semesterData["student_batch_id"];
        $schoolSemester->school_branch_id = $currentSchool->id;
        $schoolSemester->save();
        return $schoolSemester;
    }

    public function updateSchoolSemester($semesterData, $currentSchool, $schoolSemesterId){
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)->find($schoolSemesterId);
        if(!$schoolSemester){
            return ApiResponseService::error("School Semester Not Found", null, 404);
        }

        $filteredData = array_filter($semesterData);
        $schoolSemester->update($filteredData);
        return $schoolSemester;
    }

    public function deleteSchoolSemester($schoolSemesterId, $currentSchool){
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)->find($schoolSemesterId);
        if(!$schoolSemester){
            return ApiResponseService::error("School Semester Not Found", null, 404);
        }
        $schoolSemester->delete();
        return $schoolSemesterId;
    }

    public function getSchoolSemesters($currentSchool){
        $schoolSemesters = SchoolSemester::with(['specailty', 'specailty.level','semester', 'studentBatch'])->where("school_branch_id", $currentSchool->id)->get();
        return $schoolSemesters;
    }

    public function getSchoolSemesterDetail($currentSchool, $semesterId){
        $schoolSemesterDetails = SchoolSemester::with(['specailty', 'specailty.level','semester', 'studentBatch'])->where("school_branch_id", $currentSchool->id)->find($semesterId);
        return $schoolSemesterDetails;
    }
}
