<?php

namespace App\Services;

use App\Models\AccessedStudent;

class AccessedStudentService
{
    // Implement your logic here

    public function getAccessedStudents($currentSchool){
        return AccessedStudent::where("school_branch_id", $currentSchool->id)
        ->with(['student' => function ($query) {
            $query->with(['level', 'specialty']);
        }, 'exam.examtype'])
        ->paginate(100);
    }

    public function deleteAccessedStudent($accessedStudentId){
        $deleteAccessedStudent = AccessedStudent::findOrFail($accessedStudentId);
        $deleteAccessedStudent->delete();
        return $deleteAccessedStudent;
    }


}
