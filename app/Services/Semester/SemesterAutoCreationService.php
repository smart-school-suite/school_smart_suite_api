<?php

namespace App\Services\Semester;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\SchoolSemester;
use App\Services\AutoCreation\AutoCreationTargets;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Creates the automatically created copies of a newly created semester.
 * Must be called inside the transaction that created the original, so the
 * original and its copies are committed or rolled back together.
 *
 * Which specialty levels get a copy is decided by AutoCreationTargets from
 * the Semester Settings. A target is then skipped when it has no school
 * year for the original's academic year (none is created), when that
 * school year does not cover the semester's dates, or when it already has
 * this semester for that year.
 */
class SemesterAutoCreationService
{
    public function __construct(private AutoCreationTargets $targets) {}

    /** @return Collection<int, SchoolSemester> the semesters created */
    public function createFor(SchoolSemester $original, SchoolAcademicYear $originalYear): Collection
    {
        $branchId = $original->school_branch_id;
        $specialty = $originalYear->specialty;

        if (!$specialty) {
            return collect();
        }

        $targets = $this->targets->for($specialty, SemesterAutoCreationSettings::forBranch($branchId));

        $startDate = Carbon::parse($original->start_date)->startOfDay();
        $endDate = Carbon::parse($original->end_date)->startOfDay();

        $created = collect();

        foreach ($targets as $target) {
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

    private function covers(SchoolAcademicYear $schoolYear, Carbon $startDate, Carbon $endDate): bool
    {
        return $startDate->gte(Carbon::parse($schoolYear->start_date)->startOfDay())
            && $endDate->lte(Carbon::parse($schoolYear->end_date)->startOfDay());
    }
}
