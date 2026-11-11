<?php

namespace App\Services\Semester;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Creates the automatically created copies of a newly created semester.
 * Must be called inside the transaction that created the original, so the
 * original and its copies are committed or rolled back together.
 *
 * Follows Department -> Specialty -> Level. A specialty is a specialty name
 * within a department, and each of its levels is its own specialty record
 * (e.g. Software Engineering Level 1, Level 2, ...). Everything stays in
 * the original's school branch and department.
 *
 * Targets, always limited to active records at an applicable level:
 *  - the original specialty's other levels (when creation is enabled);
 *  - with Across Specialties: other specialties in the department, at the
 *    levels the original specialty also has;
 *  - with Across All Specialties' Levels: other specialties in the
 *    department, at every applicable level.
 *
 * A target is skipped when it has no school year for the original's
 * academic year (none is created), when that school year does not cover the
 * semester's dates, or when it already has this semester for that year.
 */
class SemesterAutoCreationService
{
    /** @return Collection<int, SchoolSemester> the semesters created */
    public function createFor(SchoolSemester $original, SchoolAcademicYear $originalYear): Collection
    {
        $branchId = $original->school_branch_id;
        $settings = SemesterAutoCreationSettings::forBranch($branchId);
        $specialty = $originalYear->specialty;

        if (!$settings->enabled() || empty($settings->levelIds) || !$specialty) {
            return collect();
        }

        $startDate = Carbon::parse($original->start_date)->startOfDay();
        $endDate = Carbon::parse($original->end_date)->startOfDay();

        $created = collect();

        foreach ($this->targets($specialty, $settings) as $target) {
            $schoolYear = SchoolAcademicYear::where('school_branch_id', $branchId)
                ->where('specialty_id', $target->id)
                ->where('system_academic_year_id', $originalYear->system_academic_year_id)
                ->first();

            if (!$schoolYear || !$this->covers($schoolYear, $startDate, $endDate)) {
                continue;
            }

            $exists = SchoolSemester::where('school_branch_id', $branchId)
                ->where('semester_id', $original->semester_id)
                ->where('school_year_id', $schoolYear->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $created->push(SchoolSemester::create([
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'school_year_id' => $schoolYear->id,
                'semester_id' => $original->semester_id,
                'school_branch_id' => $branchId,
                'timetable_published' => false,
            ]));
        }

        return $created;
    }

    private function targets(Specialty $specialty, SemesterAutoCreationSettings $settings): Collection
    {
        $sameSpecialty = $this->candidates($specialty)
            ->where('specialty_name', $specialty->specialty_name)
            ->whereIn('level_id', $settings->levelIds)
            ->get();

        if (!$settings->includesOtherSpecialties()) {
            return $sameSpecialty;
        }

        $others = $this->candidates($specialty)
            ->where('specialty_name', '!=', $specialty->specialty_name)
            ->whereIn('level_id', $settings->levelIds);

        if (!$settings->acrossAllLevels) {
            // Matching academic structure: only the levels the original specialty has.
            $others->whereIn('level_id', $this->candidates($specialty, includeOriginal: true)
                ->where('specialty_name', $specialty->specialty_name)
                ->select('level_id'));
        }

        return $sameSpecialty->concat($others->get());
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

    private function covers(SchoolAcademicYear $schoolYear, Carbon $startDate, Carbon $endDate): bool
    {
        return $startDate->gte(Carbon::parse($schoolYear->start_date)->startOfDay())
            && $endDate->lte(Carbon::parse($schoolYear->end_date)->startOfDay());
    }
}
