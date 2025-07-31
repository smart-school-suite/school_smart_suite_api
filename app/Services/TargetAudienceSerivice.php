<?php

namespace App\Services;
use App\Models\Educationlevels;
use App\Models\Department;
use App\Models\Specialty;
class TargetAudienceSerivice
{
    public function getAnnouncementTargetAudience($currentSchool)
    {
        $levels = Educationlevels::all();
        $specialties = Specialty::where("school_branch_id", $currentSchool->id)
             ->with(['level'])
            ->get();
        $departments  = Department::where("school_branch_id", $currentSchool->id)
            ->select('department_name', 'id')
            ->get();

        $studentTargets = [];
        $parentTargets = [];

        foreach ($levels as $level) {
            $studentTargets[] = [
                'title' => "{$level->name} Students",
                'id' => $level->id,
                'level_name' => $level->name,
                'level' => $level->level,
            ];
            $parentTargets[] = [
                'title' => "Guardians Of {$level->name} Students",
                'id' => $level->id,
                'level_name' => $level->name,
                'level' => $level->level,
            ];
        }

        foreach ($specialties as $specialty) {
            $studentTargets[] = [
                'title' => "{$specialty->level->name} {$specialty->specialty_name} Students",
                'id' => $specialty->id,
                'specialty_name' =>  $specialty->specialty_name,
                'level_name' => $specialty->level->name ?? null,
                'level' => $specialty->level->level ?? null,
            ];
            $parentTargets[] = [
                'title' => "Guardians Of {$specialty->level->name}  {$specialty->specialty_name} Students",
                'id' => $specialty->id,
                'specialty_name' =>  $specialty->specialty_name,
                'level_name' => $specialty->level->name ?? null,
                'level' => $specialty->level->level ?? null,
            ];
        }

        foreach ($departments as $department) {
            $studentTargets[] = [
                'title' => "{$department->department_name} students",
                'id' => $department->id,
                'department_name' => $department->department_name,
            ];
            $parentTargets[] = [
                'title' => "Guardians Of {$department->department_name} Students",
                'id' => $department->id,
                'department_name' => $department->department_name,
            ];
        }


        return [
            'student_target' => $studentTargets,
            'parent_target' => $parentTargets,
        ];
    }
}
