<?php

namespace App\Services;

use App\Models\StudentResults;

class StudentResultService
{
    // Implement your logic heres
    public function getAllStudentResults($currentSchool)
    {
        // Logic to get all student results
        $studentResults = StudentResults::where('school_branch_id', $currentSchool->id)
                                         ->with(['student', 'specialty', 'level', 'exam.ex', 'studentBatch'])
                                         ->get();
        return $studentResults;
    }


}
