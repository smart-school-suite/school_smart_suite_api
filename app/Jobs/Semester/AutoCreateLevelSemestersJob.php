<?php

namespace App\Jobs\Semester;

use App\Jobs\DataCreationJob\CreateInstructorAvailabilityJob;
use App\Jobs\JointCourse\CreateJointCourseSemesterJob;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use App\Models\Student;
use App\Services\Semester\SemesterAutoCreateSetting;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * After a semester is created for one level of a specialty (e.g. First
 * Semester for Software Engineering Level 1), creates the same semester,
 * with the same dates, for the specialty's other levels in the same
 * academic year, according to the school branch's setting:
 *
 *  - semester.auto_create_all_levels:          every other level.
 *  - semester.auto_create_levels_with_students: only levels with active students.
 *
 * A level is skipped when it has no school year for that academic year,
 * when the semester dates fall outside that school year, or when the
 * semester already exists there.
 */
class AutoCreateLevelSemestersJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        protected string $schoolBranchId,
        protected string $schoolSemesterId,
    ) {}

    public function handle(SemesterAutoCreateSetting $setting): void
    {
        $mode = $setting->modeFor($this->schoolBranchId);
        if ($mode === null) {
            return;
        }

        $source = SchoolSemester::where('school_branch_id', $this->schoolBranchId)
            ->with('schoolYear.specialty')
            ->find($this->schoolSemesterId);

        $specialty = $source?->schoolYear?->specialty;
        if (!$specialty) {
            Log::warning("School semester {$this->schoolSemesterId} or its specialty was not found; no semesters auto created for other levels.");
            return;
        }

        $startDate = Carbon::parse($source->start_date)->startOfDay();
        $endDate = Carbon::parse($source->end_date)->startOfDay();

        foreach ($this->otherLevelSpecialties($specialty) as $levelSpecialty) {
            if ($mode === SemesterAutoCreateSetting::LEVELS_WITH_STUDENTS && !$this->hasActiveStudents($levelSpecialty)) {
                continue;
            }

            $schoolYear = SchoolAcademicYear::where('school_branch_id', $this->schoolBranchId)
                ->where('specialty_id', $levelSpecialty->id)
                ->where('system_academic_year_id', $source->schoolYear->system_academic_year_id)
                ->first();

            if (!$schoolYear || !$this->fitsInside($schoolYear, $startDate, $endDate)) {
                continue;
            }

            $alreadyExists = SchoolSemester::where('school_branch_id', $this->schoolBranchId)
                ->where('semester_id', $source->semester_id)
                ->where('school_year_id', $schoolYear->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $this->createSemester($source, $schoolYear, $startDate, $endDate);
        }
    }

    /** The same specialty (name and department) at its other, active levels. */
    private function otherLevelSpecialties(Specialty $specialty)
    {
        return Specialty::where('school_branch_id', $this->schoolBranchId)
            ->where('specialty_name', $specialty->specialty_name)
            ->where('department_id', $specialty->department_id)
            ->where('id', '!=', $specialty->id)
            ->where('level_id', '!=', $specialty->level_id)
            ->where(fn($query) => $query->whereNull('status')->orWhere('status', '!=', 'inactive'))
            ->get();
    }

    private function hasActiveStudents(Specialty $specialty): bool
    {
        return Student::where('school_branch_id', $this->schoolBranchId)
            ->where('specialty_id', $specialty->id)
            ->where(fn($query) => $query->whereNull('dropout_status')->orWhere('dropout_status', false))
            ->exists();
    }

    private function fitsInside(SchoolAcademicYear $schoolYear, Carbon $startDate, Carbon $endDate): bool
    {
        return $startDate->gte(Carbon::parse($schoolYear->start_date)->startOfDay())
            && $endDate->lte(Carbon::parse($schoolYear->end_date)->startOfDay());
    }

    private function createSemester(SchoolSemester $source, SchoolAcademicYear $schoolYear, Carbon $startDate, Carbon $endDate): void
    {
        try {
            $schoolSemester = SchoolSemester::create([
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'school_year_id' => $schoolYear->id,
                'semester_id' => $source->semester_id,
                'school_branch_id' => $this->schoolBranchId,
                'timetable_published' => false,
            ]);
        } catch (Throwable $e) {
            Log::error("Failed to auto create semester {$source->semester_id} for school year {$schoolYear->id}", [
                'source_school_semester_id' => $source->id,
                'message' => $e->getMessage(),
            ]);
            return;
        }

        // Same follow-up work as a semester created by hand. This job is not
        // dispatched again, so auto-created semesters never cascade.
        CreateInstructorAvailabilityJob::dispatch($this->schoolBranchId, $schoolSemester->id);
        CreateJointCourseSemesterJob::dispatch($schoolSemester->id, (object) ['id' => $this->schoolBranchId]);
    }
}
