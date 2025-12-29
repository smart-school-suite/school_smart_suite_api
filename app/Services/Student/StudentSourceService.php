<?php

namespace App\Services\Student;
use App\Models\StudentSource;
class StudentSourceService
{
    public function getAllStudentSource(){
        $studentSources = StudentSource::all();
        return $studentSources;
    }

    public function getActiveStudentSource(){
         $studentSources = StudentSource::where("status", "active")
          ->get();
          return $studentSources;
    }

    public function updateStudentSource($updateData, $studentSourceId){
        $studentSource= StudentSource::find($studentSourceId);
        $cleanData = array_filter($updateData);

    }
}
