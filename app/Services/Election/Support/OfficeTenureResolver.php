<?php

namespace App\Services\Election\Support;

use App\Exceptions\AppException;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Election\ElectionOfficeTerm;
use App\Models\Election\ElectionRole;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Resolves the school academic years (specialty specific) that make up an
 * office holder's tenure and validates the academic-year span.
 *
 * A tenure of N academic years starting at system year Y covers Y and the
 * following N-1 consecutive system academic years. For every system year the
 * matching school_academic_years record is used:
 *  - students: the record for the student's specialty;
 *  - teachers / school admins: the branch record that ends last, so the
 *    tenure only completes once every specialty's academic year has ended.
 */
class OfficeTenureResolver
{
    public function termFor(ElectionRole $position): ElectionOfficeTerm
    {
        return $position->officeTerm ?? new ElectionOfficeTerm([
            'school_branch_id' => $position->school_branch_id,
            'election_role_id' => $position->id,
            'duration_academic_years' => 1,
            'allow_reelection' => false,
            'maximum_terms' => null,
        ]);
    }

    /**
     * The consecutive system academic years of a tenure.
     */
    public function systemYearSpan(SystemAcademicYear $startYear, int $duration): Collection
    {
        $span = SystemAcademicYear::query()
            ->where('year_start', '>=', $startYear->year_start)
            ->orderBy('year_start')
            ->limit($duration)
            ->get();

        $expectedStart = (int) $startYear->year_start;
        foreach ($span->values() as $index => $year) {
            if ((int) $year->year_start !== $expectedStart + $index) {
                $span = $span->take($index);
                break;
            }
        }

        if ($span->count() < $duration) {
            $missingFrom = $expectedStart + $span->count();
            throw new AppException(
                "Academic year span incomplete",
                422,
                "Missing Academic Years",
                "This position is held for {$duration} academic year(s) starting {$startYear->name}, but the system academic year starting {$missingFrom} does not exist yet.",
                null
            );
        }

        return $span->values();
    }

    /**
     * Resolve the school academic years for a holder.
     *
     * @return Collection<int, SchoolAcademicYear> ordered by tenure year
     */
    public function resolveSchoolYears(Model $holder, string $schoolBranchId, SystemAcademicYear $startYear, int $duration): Collection
    {
        return $this->systemYearSpan($startYear, $duration)->map(function (SystemAcademicYear $systemYear) use ($holder, $schoolBranchId) {
            $query = SchoolAcademicYear::query()
                ->where('school_branch_id', $schoolBranchId)
                ->where('system_academic_year_id', $systemYear->id);

            if ($holder instanceof Student) {
                $query->where('specialty_id', $holder->specialty_id);
            } else {
                $query->orderByDesc('end_date');
            }

            $schoolYear = $query->first();

            if (!$schoolYear) {
                $who = $holder instanceof Student ? "the holder's specialty" : 'this school branch';
                throw new AppException(
                    "School academic year not configured",
                    422,
                    "Missing School Academic Year",
                    "No school academic year exists for {$who} in {$systemYear->name}. Configure it before assigning this office.",
                    null
                );
            }

            return $schoolYear;
        })->values();
    }

    /**
     * Validate explicitly supplied school academic years against the span.
     *
     * @param array<int, string> $schoolYearIds ordered by tenure year
     */
    public function validateSchoolYears(array $schoolYearIds, Model $holder, string $schoolBranchId, SystemAcademicYear $startYear, int $duration): Collection
    {
        if (count($schoolYearIds) !== $duration) {
            throw new AppException(
                "Invalid tenure years",
                422,
                "Invalid Academic-Year Span",
                "This position is held for {$duration} academic year(s); " . count($schoolYearIds) . " were provided.",
                null
            );
        }

        $span = $this->systemYearSpan($startYear, $duration);
        $years = SchoolAcademicYear::whereIn('id', $schoolYearIds)->get()->keyBy('id');

        return collect($schoolYearIds)->values()->map(function ($id, $index) use ($years, $span, $schoolBranchId, $holder) {
            $year = $years->get($id);
            $expected = $span[$index];

            if (!$year || $year->school_branch_id !== $schoolBranchId) {
                throw new AppException("School academic year not found", 404, "Academic Year Not Found",
                    "One of the selected school academic years does not belong to this school branch.", null);
            }
            if ($year->system_academic_year_id !== $expected->id) {
                throw new AppException("Academic year outside tenure span", 422, "Invalid Academic-Year Span",
                    "Tenure year " . ($index + 1) . " must belong to {$expected->name}.", null);
            }
            if ($holder instanceof Student && $year->specialty_id !== $holder->specialty_id) {
                throw new AppException("Academic year specialty mismatch", 422, "Invalid Academic Year",
                    "The selected academic year for {$expected->name} does not belong to the holder's specialty.", null);
            }
            return $year;
        });
    }
}
