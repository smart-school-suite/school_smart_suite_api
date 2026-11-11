<?php

namespace App\Services\AutoCreation;

use App\Models\Specialty;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Which specialty levels receive an automatically created record, given the
 * specialty it was created for. Follows Department -> Specialty -> Level: a
 * specialty is a specialty name within a department, and each of its levels
 * is its own specialty record. Everything stays in the original's school
 * branch and department, and only active records at an applicable level
 * are returned:
 *
 *  - the original specialty's other levels (when creation is enabled);
 *  - with Across Specialties: other specialties in the department, at the
 *    levels the original specialty also has;
 *  - with Across All Specialties' Levels: other specialties in the
 *    department, at every applicable level;
 *  - with Only Include Specialties with Enrolled Students: only targets
 *    with at least one enrolled (non dropped out) student.
 */
class AutoCreationTargets
{
    public function for(Specialty $specialty, AutoCreationSettings $settings): Collection
    {
        if (!$settings->enabled() || empty($settings->levelIds)) {
            return collect();
        }

        $targets = $this->candidates($specialty)
            ->where('specialty_name', $specialty->specialty_name)
            ->whereIn('level_id', $settings->levelIds)
            ->get();

        if ($settings->includesOtherSpecialties()) {
            $others = $this->candidates($specialty)
                ->where('specialty_name', '!=', $specialty->specialty_name)
                ->whereIn('level_id', $settings->levelIds);

            if (!$settings->acrossAllLevels) {
                // Matching academic structure: only the levels the original specialty has.
                $others->whereIn('level_id', $this->candidates($specialty, includeOriginal: true)
                    ->where('specialty_name', $specialty->specialty_name)
                    ->select('level_id'));
            }

            $targets = $targets->concat($others->get());
        }

        if ($settings->requireStudents) {
            $targets = $targets->filter(fn(Specialty $target) => $this->hasEnrolledStudents($target))->values();
        }

        return $targets;
    }

    /** Active specialty records of the original's branch and department. */
    private function candidates(Specialty $specialty, bool $includeOriginal = false): Builder
    {
        return Specialty::query()
            ->where('school_branch_id', $specialty->school_branch_id)
            ->where('department_id', $specialty->department_id)
            ->when(!$includeOriginal, fn($query) => $query->where('id', '!=', $specialty->id))
            ->where(fn($query) => $query->whereNull('status')->orWhere('status', '!=', 'inactive'));
    }

    private function hasEnrolledStudents(Specialty $specialty): bool
    {
        return Student::where('school_branch_id', $specialty->school_branch_id)
            ->where('specialty_id', $specialty->id)
            ->where(fn($query) => $query->whereNull('dropout_status')->orWhere('dropout_status', false))
            ->exists();
    }
}
