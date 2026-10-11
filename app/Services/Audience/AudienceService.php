<?php

namespace App\Services\Audience;

use App\Models\Department;
use App\Models\Specialty;
use App\Models\EducationLevels;
use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use App\Models\Student;
use App\Models\Teacher;

class AudienceService
{
    public function getStudentAudienceBySpecialty(object $currentSchool)
    {
        return Specialty::where("school_branch_id", $currentSchool->id)
            ->withCount(['student as students_count'])
            ->with('level')
            ->get()
            ->map(function ($specialty) {
                return [
                    'id' => $specialty->id,
                    'specialty_name' => $specialty?->specialty_name,
                    'student_count' => $specialty->students_count ?? 0,
                    'level_name' => $specialty?->level?->name,
                    'level' => $specialty?->level?->level
                ];
            });
    }

    public function getStudentAudienceByLevel(object $currentSchool)
    {
        return EducationLevels::whereHas('specialty', function ($query) use ($currentSchool) {
            $query->where('school_branch_id', $currentSchool->id);
        })
            ->withCount(['students' => function ($query) use ($currentSchool) {
                $query->where('students.school_branch_id', $currentSchool->id);
            }])
            ->get()
            ->map(function ($level) {
                return [
                    'id' => $level->id,
                    'level_name' => $level?->name,
                    'level' => $level?->level,
                    'student_count' => $level?->students_count ?? 0,
                ];
            });
    }

    public function getStudentAudienceByDepartment(object $currentSchool)
    {
        return Department::where("departments.school_branch_id", $currentSchool->id)
            ->withCount(['students' => function ($query) use ($currentSchool) {
                $query->where('students.school_branch_id', $currentSchool->id);
            }])
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'department_name' => $department?->department_name,
                    'student_count' => $department?->students_count,
                ];
            });
    }

    public function getStudents(object $currentSchool)
    {
        return Student::where("school_branch_id", $currentSchool->id)
            ->with(['specialty.level'])
            ->get()->map(fn($s) => [
                'id' => $s->id,
                'username' => $s->username,
                'first_name' => $s->first_name,
                'last_name' => $s->last_name,
                'name' => $s->name,
                'profile_picture' => $s?->profile_picture,
                'specialty' => $s?->specialty?->specialty_name,
                "level" => $s->specialty->level->level,
                "level_name" => $s->specialty->level->name
            ]);
    }

    public function getTeacherAudience(object $currentSchool)
    {
       return Teacher::where("school_branch_id", $currentSchool->id)
            ->get()
            ->map(fn($t) => [
                "id" => $t->id,
                "first_name" => $t->first_name,
                "last_name" => $t->last_name,
                "username" => $t->username,
                "name" => $t->name,
                "profile_picture" => $t->profile_picture
            ]);
    }

    public function getTeacherAudienceByDepartment(object $currentSchool)
    {
        return Department::where("school_branch_id", $currentSchool->id)
            ->with(['specialty.teachers'])
            ->get()
            ->map(function ($department) {
                $uniqueTeacherCount = $department->specialty
                    ->flatMap->teachers
                    ->unique('id')
                    ->count();

                return [
                    'id' => $department->id,
                    'department_name' => $department->department_name,
                    'teacher_count' => $uniqueTeacherCount,
                ];
            });
    }

    public function getTeacherAudienceBySpecialty(object $currentSchool)
    {
        return Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level'])
            ->withCount(['teachers'])
            ->get()
            ->map(function ($specialty) {
                return [
                    'id' => $specialty->id,
                    'level_name' => $specialty?->level?->name,
                    'level' => $specialty?->level?->level,
                    'specialty_name' => $specialty?->specialty_name,
                    'teacher_count' => $specialty?->teachers_count,
                ];
            });
    }

    public function getTeacherAudienceByLevel(object $currentSchool)
    {
        return EducationLevels::whereHas('specialty', function ($query) use ($currentSchool) {
            $query->where('school_branch_id', $currentSchool->id);
        })
            ->with(['specialty.teachers'])
            ->get()
            ->map(function ($level) {
                $uniqueTeacherCount = $level->specialty
                    ->flatMap->teachers
                    ->unique('id')
                    ->count();

                return [
                    'id' => $level->id,
                    'level_name' => $level?->name,
                    'level' => $level?->level,
                    'teacher_count' => $uniqueTeacherCount,
                ];
            });
    }


    public function getSchoolAdminAudience(object $currentSchool)
    {
        return Schooladmin::where("school_branch_id", $currentSchool->id)
            ->get()
            ->map(fn($a) => [
                "profile_picture" => $a->profile_picture,
                'username' => $a->username,
                'name' => $a->name,
                'first_name' => $a->first_name,
                'last_name' => $a->last_name
            ]);
    }

    public function getAudienceSummary(object $currentSchool)
    {
        $branch = Schoolbranches::where("id", $currentSchool->id)
            ->withCount([
                'student as students_count',
                'teacher as teachers_count',
                'schooladmin as school_admins_count',
            ])
            ->firstOrFail();

        $studentCount = (int) $branch->students_count;
        $teacherCount = (int) $branch->teachers_count;
        $adminCount   = (int) $branch->school_admins_count;

        return [
            'students'       => $studentCount,
            'teachers'       => $teacherCount,
            'schoolAdmins'  => $adminCount,
            'school_wide'      => $studentCount + $teacherCount + $adminCount,
        ];
    }
}
