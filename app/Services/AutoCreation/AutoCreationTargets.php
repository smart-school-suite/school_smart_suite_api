<?php

namespace App\Services\AutoCreation;

use App\Models\Specialty;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Which specialty levels receive an automatically created record, given the
 * specialty it was created for. Only active specialties of the same school
 * branch, at an applicable level, are returned. The scope narrows them to
 * the same specialty name, the same department, or both. With Only Include
 * Specialties with Enrolled Students, targets without enrolled (non dropped
 * out) students are left out.
 */
class AutoCreationTargets
{
    public function for(Specialty $specialty, AutoCreationSettings $settings): Collection
    {
        if (!$settings->enabled() || empty($settings->levelIds)) {
            return collect();
        }

        $targets = Specialty::query()
            ->where('school_branch_id', $specialty->school_branch_id)
            ->where('id', '!=', $specialty->id)
            ->whereIn('level_id', $settings->levelIds)
            ->where(fn($query) => $query->whereNull('status')->orWhere('status', '!=', 'inactive'))
            ->when($settings->sameDepartment(), fn($query) => $query->where('department_id', $specialty->department_id))
            ->when($settings->sameSpecialty(), fn($query) => $query->where('specialty_name', $specialty->specialty_name))
            ->get();

        if ($settings->requireStudents) {
            $targets = $targets->filter(fn(Specialty $target) => $this->hasEnrolledStudents($target))->values();
        }

        return $targets;
    }

    private function hasEnrolledStudents(Specialty $specialty): bool
    {
        return Student::where('school_branch_id', $specialty->school_branch_id)
            ->where('specialty_id', $specialty->id)
            ->where(fn($query) => $query->whereNull('dropout_status')->orWhere('dropout_status', false))
            ->exists();
    }
}
