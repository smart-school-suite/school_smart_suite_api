<?php

namespace App\Services;
use App\Models\Teacher;
class TeacherService
{
    // Implement your logic here
    public function getTeacherDetails($teacher_id){
        $find_teacher = Teacher::findOrFail($teacher_id);
        return $find_teacher;
    }
    public function deletetTeacher($teacher_id){
        $teacher = Teacher::findOrFail($teacher_id);
        $teacher->delete();
        return $teacher;
    }
    public function updateTeacher(array $data, $teacher_id){
        $teacher = Teacher::findOrFail($teacher_id);
        $filterData = array_filter($data);
        $teacher->update($filterData);
        return $teacher;
    }

}
