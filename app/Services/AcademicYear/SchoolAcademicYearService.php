<?php

namespace App\Services\AcademicYear;

use App\Events\AcademicYear\SchoolAcademicYearCreated;
use App\Exceptions\AppException;
use App\Models\Specialty;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Promotion\PromotionRun;
use App\Models\FeeSchedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchoolAcademicYearService
{
    public function createSchoolAcademicYear(array $data, object $currentSchool): SchoolAcademicYear
    {
        $specialty = Specialty::with(['student'])
            ->where('school_branch_id', $currentSchool->id)
            ->find($data['specialty_id']);

        if (!$specialty) {
            throw new AppException(
                'The requested specialty could not be found.',
                404,
                'Specialty Not Found',
                "No specialty found with ID: {$data['specialty_id']}."
            );
        }

        $systemAcademicYear = SystemAcademicYear::find($data['system_academic_year_id']);

        if (!$systemAcademicYear) {
            throw new AppException(
                'The requested system academic year could not be found.',
                404,
                'System Academic Year Not Found',
                "No system academic year found with ID: {$data['system_academic_year_id']}."
            );
        }

        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            throw new AppException(
                'The academic year start date must be prior to the end date.',
                422,
                'Invalid Date Range',
                "Provided start date ({$data['start_date']}) cannot be on or after the end date ({$data['end_date']})."
            );
        }

        $existingAcademicYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->where('system_academic_year_id', $data['system_academic_year_id'])
            ->where('specialty_id', $data['specialty_id'])
            ->with(['systemAcademicYear', 'specialty.level'])
            ->first();

        if ($existingAcademicYear) {
            throw new AppException(
                'An academic year with the same school year and specialty already exists for this school branch.',
                409,
                'Existing Academic Year',
                "Academic Year {$existingAcademicYear->systemAcademicYear?->name} for {$existingAcademicYear->specialty?->specialty_name}, {$existingAcademicYear->specialty?->level?->level} already exists."
            );
        }

        // The original and its automatically created copies commit or roll back together.
        return DB::transaction(function () use ($data, $currentSchool, $specialty) {
            $academicYear = SchoolAcademicYear::create([
                'specialty_id' => $data['specialty_id'],
                'school_branch_id' => $currentSchool->id,
                'system_academic_year_id' => $data['system_academic_year_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);

            FeeSchedule::create([
                'school_branch_id' => $currentSchool->id,
                'school_year_id' => $academicYear->id,
            ]);

            PromotionRun::create([
                'school_year_id' => $academicYear->id,
                'school_branch_id' => $currentSchool->id,
                'total_students' => $specialty->student->count(),
            ]);

            $autoCreated = app(SchoolAcademicYearAutoCreationService::class)->createFor($academicYear, $specialty);

            // Bills each academic year's students once the transaction commits.
            foreach ([$academicYear, ...$autoCreated] as $created) {
                SchoolAcademicYearCreated::dispatch($created);
            }

            return $academicYear->setRelation('autoCreatedAcademicYears', $autoCreated);
        });
    }

    /**
     * Applies the changes SchoolAcademicYearUpdatePolicy allows, and returns
     * the updated academic year with its rules after the change.
     */
    public function updateSchoolAcademicYear(
        object $currentSchool,
        string $schoolAcademicYearId,
        array $data
    ) {
        $academicYear = $this->findForBranch($currentSchool, $schoolAcademicYearId);
        $policy = app(SchoolAcademicYearUpdatePolicy::class);

        DB::transaction(function () use ($academicYear, $policy, $data) {
            // Locked so two admins editing at once are checked one after the other.
            $locked = SchoolAcademicYear::lockForUpdate()->find($academicYear->id);
            $locked->update($policy->validatedChanges($locked, $data));
        });

        $academicYear->refresh()->load(['systemAcademicYear', 'specialty.level']);

        return [
            ...$academicYear->toArray(),
            'update_rules' => $policy->describe($academicYear),
        ];
    }

    public function getUpdateRules(object $currentSchool, string $schoolAcademicYearId): array
    {
        return app(SchoolAcademicYearUpdatePolicy::class)
            ->describe($this->findForBranch($currentSchool, $schoolAcademicYearId));
    }

    private function findForBranch(object $currentSchool, string $schoolAcademicYearId): SchoolAcademicYear
    {
        $academicYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->with(['systemAcademicYear', 'specialty.level'])
            ->find($schoolAcademicYearId);

        if (!$academicYear) {
            throw new AppException(
                'School Academic Year not found.',
                404,
                'Not Found',
                'The school academic year does not exist or has been deleted.'
            );
        }

        return $academicYear;
    }

    public function deleteSchoolAcademicYear(
        object $currentSchool,
        string $schoolAcademicYearId
    ) {
        $academicYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->find($schoolAcademicYearId);

        if (!$academicYear) {
            throw new AppException(
                'School Academic Year not found.',
                404,
                'Not Found',
                'The school academic year does not exist or has been deleted.'
            );
        }

        $now = now();

        if ($academicYear->start_date->lte($now)) {
            throw new AppException(
                'Academic year deletion forbidden',
                409,
                'Academic Year Locked',
                'You cannot delete an academic year that has already started or ended.'
            );
        }

        $academicYear->delete();

        return true;
    }

    public function getSchoolAcademicYears(object $currentSchool)
    {
        $schoolAcademicYears = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->with(['systemAcademicYear', 'specialty.level'])
            ->orderBy('start_date', 'desc')
            ->get();

        if ($schoolAcademicYears->isEmpty()) {
            throw new AppException(
                'No School Academic Years found for the given school branch.',
                404,
                "School Academic Years Not Found",
                "Not School Academic Year Found For this School Branch Please Create An Academic Year to Continue"
            );
        }

        $today = now()->startOfDay();

        $schoolAcademicYears = $schoolAcademicYears->map(function ($year) use ($today) {
            $start = Carbon::parse($year->start_date)->startOfDay();
            $end   = Carbon::parse($year->end_date)->endOfDay();

            $status = match (true) {
                $today < $start  => 'upcoming',
                $today <= $end   => 'ongoing',
                default          => 'expired'
            };

            return [
                "id" => $year->id,
                "start_date" => $year->start_date,
                "end_date" => $year->end_date,
                "specialty_name" => $year->specialty->specialty_name ?? null,
                "level_name" => $year->specialty->level->name ?? null,
                "level_number" => $year->specialty->level->level ?? null,
                "status" => $status,
                "school_year" => $year->systemAcademicYear->name ?? null,
                "year_start" => $year->systemAcademicYear->year_start ?? null,
                "year_end" => $year->systemAcademicYear->year_end ?? null,
                "created_at" => $year->created_at ?? null,
                "updated_at" => $year->updated_at ?? null,
            ];
        });

        return $schoolAcademicYears;
    }

    public function getSchoolAcademicYearById(object $currentSchool, string $schoolAcademicYearId)
    {
        $academicYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->with(['systemAcademicYear', 'specialty.level'])
            ->find($schoolAcademicYearId);

        if (!$academicYear) {
            throw new AppException(
                'School Academic Year not found for the given school branch.',
                404,
                "Not Found",
                "School Academic Year Not Found For this School Branch, Ensure that it has not been deleted and try again"
            );
        }

        return $academicYear;
    }


}
