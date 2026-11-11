<?php

namespace App\Jobs\Semester;

use App\Jobs\DataCreationJob\CreateInstructorAvailabilityJob;
use App\Jobs\JointCourse\CreateJointCourseSemesterJob;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use App\Models\Student;
use App\Services\Semester\SemesterSyncSettings;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Semester synchronization. After a semester is created for one specialty
 * level (e.g. First Semester for Software Engineering Level 1), creates the
 * same semester, with the same dates, for the other specialties the
 * branch's semester settings select (see SemesterSyncSettings).
 *
 * A target specialty is skipped when it is inactive, its level is not an
 * allowed synchronization level, it has no active students while students
 * are required, it has no school year for the same academic year, that
 * school year does not cover the dates, or the semester already exists.
 * The semester the admin created is never affected by these settings.
 */
class SynchronizeSemesterJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        protected string $schoolBranchId,
        protected string $schoolSemesterId,
    ) {}

    public function handle(): void
    {
        $settings = SemesterSyncSettings::forBranch($this->schoolBranchId);
        if (!$settings->enabled() || empty($settings->allowedLevelIds)) {
            return;
        }

        $source = SchoolSemester::where('school_branch_id', $this->schoolBranchId)
            ->with('schoolYear.specialty')
            ->find($this->schoolSemesterId);

        $specialty = $source?->schoolYear?->specialty;
        if (!$specialty) {
            Log::warning("School semester {$this->schoolSemesterId} or its specialty was not found; semester not synchronized.");
            return;
        }

        $startDate = Carbon::parse($source->start_date)->startOfDay();
        $endDate = Carbon::parse($source->end_date)->startOfDay();

        foreach ($this->targetSpecialties($specialty, $settings) as $target) {
            if ($settings->requireStudents && !$this->hasActiveStudents($target)) {
                continue;
            }

            $schoolYear = SchoolAcademicYear::where('school_branch_id', $this->schoolBranchId)
                ->where('specialty_id', $target->id)
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

    /** Active specialties, other than the source, at an allowed level, within the mode's boundary. */
    private function targetSpecialties(Specialty $source, SemesterSyncSettings $settings): Collection
    {
        $query = Specialty::where('school_branch_id', $this->schoolBranchId)
            ->where('id', '!=', $source->id)
            ->whereIn('level_id', $settings->allowedLevelIds)
            ->where(fn($q) => $q->whereNull('status')->orWhere('status', '!=', 'inactive'));

        if ($settings->mode === SemesterSyncSettings::MODE_SAME_PROGRAMME) {
            return $query->where('specialty_name', $source->specialty_name)
                ->where('department_id', $source->department_id)
                ->get();
        }

        match ($settings->scope) {
            SemesterSyncSettings::SCOPE_SAME_SPECIALTY => $query->where('specialty_name', $source->specialty_name),
            SemesterSyncSettings::SCOPE_ACROSS_SPECIALTIES => $query->where('department_id', $source->department_id),
            SemesterSyncSettings::SCOPE_INSTITUTION => $query,
        };

        return $query->get();
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
            Log::error("Failed to synchronize semester {$source->semester_id} to school year {$schoolYear->id}", [
                'source_school_semester_id' => $source->id,
                'message' => $e->getMessage(),
            ]);
            return;
        }

        // Same follow-up work as a semester created by hand. This job is not
        // dispatched again, so synchronized semesters never cascade.
        CreateInstructorAvailabilityJob::dispatch($this->schoolBranchId, $schoolSemester->id);
        CreateJointCourseSemesterJob::dispatch($schoolSemester->id, (object) ['id' => $this->schoolBranchId]);
    }
}
