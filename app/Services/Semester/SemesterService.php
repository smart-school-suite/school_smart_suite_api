<?php

namespace App\Services\Semester;
use App\Models\Semester;
class SemesterService
{
       public function createSemester(array $data){
        $createSemester = Semester::create($data);
        return $createSemester;
    }

    public function deleteSemester($semsterId){
        $semster = Semester::findOrFail($semsterId);
        $semster->delete();
        return $semster;
    }

    public function updateSemester($semsterId, array $data){
        $semester = Semester::findOrFail($semsterId);
        $filterData = array_filter($data);
        $semester->update($filterData );
        return $semester;
    }

    public function getSemester($currentSchool){
        $numSemesters = $currentSchool->semester_count;
        $filteredSemesters = Semester::whereBetween('count', [1, $numSemesters])->get();
        return $filteredSemesters;
    }
}
