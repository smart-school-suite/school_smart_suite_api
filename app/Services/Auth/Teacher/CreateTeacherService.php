<?php

namespace App\Services\Auth\Teacher;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
class CreateTeacherService
{
    // Implement your logic here
    public function createInstructor($teacherData, $currentSchool){
        $new_teacher_instance = new Teacher();
        $new_teacher_instance->name = $teacherData["name"];
        $new_teacher_instance->email = $teacherData["email"];
        $new_teacher_instance->password = Hash::make($teacherData["password"]);
        $new_teacher_instance->phone_one = $teacherData["phone_one"];
        $new_teacher_instance->employment_status = $teacherData["employment_status"];
        $new_teacher_instance->highest_qualification = $teacherData["highest_qualification"];
        $new_teacher_instance->field_of_study = $teacherData["field_of_study"];
        $new_teacher_instance->years_experience = $teacherData["years_experience"];
        $new_teacher_instance->salary = $teacherData["salary"];
        $new_teacher_instance->school_branch_id = $currentSchool->id;
        $new_teacher_instance->save();
        return $new_teacher_instance;
    }
}
