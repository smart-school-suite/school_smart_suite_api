<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Courses; // Ensure this model is correctly configured
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Str;

class CourseStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * The ID of the course.
     * @var string
     */
    protected readonly string $courseId;

    /**
     * Create a new job instance.
     *
     * @param string $schoolBranchId The ID of the school branch.
     * @param string $courseId The ID of the course.
     */
    public function __construct(string $schoolBranchId, string $courseId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->courseId = $courseId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {

        $kpiNames = [
            'total_courses_count',
            'total_courses_count_by_specialty',
            'total_courses_count_by_department'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $courseDetails = Courses::where("school_branch_id", $this->schoolBranchId)->find($this->courseId);

        if (!$courseDetails) {
            Log::warning("Course with ID '{$this->courseId}' not found in school branch '{$this->schoolBranchId}'. Skipping course stats job.");
            return;
        }

        $this->upsertCourseStat(
            $this->schoolBranchId,
            $kpis->get('total_courses_count')
        );

        if ($courseDetails->specialty_id) {
            $this->upsertCourseStat(
                $this->schoolBranchId,
                $kpis->get('total_courses_count_by_specialty'),
                $courseDetails->specialty_id,
                null
            );
        } else {
            Log::info("Course '{$this->courseId}' has no specialty ID. Skipping specialty-based course count.");
        }

        if ($courseDetails->department_id) {
            $this->upsertCourseStat(
                $this->schoolBranchId,
                $kpis->get('total_courses_count_by_department'),
                null,
                $courseDetails->department_id
            );
        } else {
            Log::info("Course '{$this->courseId}' has no department ID. Skipping department-based course count.");
        }
    }

    /**
     * Inserts or updates a course statistic record, incrementing its integer_value.
     *
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes|null $kpi The StatTypes model instance for the KPI, or null if not found.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @return void
     */
    private function upsertCourseStat(
        string $schoolBranchId,
        ?StatTypes $kpi,
        ?string $specialtyId = null,
        ?string $departmentId = null
    ): void {
        if (!$kpi) {
            Log::warning("StatType for KPI not found. Cannot record course stat for school: {$schoolBranchId}.");
            return;
        }

        $matchCriteria = [
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
        ];

        if ($specialtyId) {
            $matchCriteria['specialty_id'] = $specialtyId;
        }
        if ($departmentId) {
            $matchCriteria['department_id'] = $departmentId;
        }


        $existingStat = DB::table('course_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('course_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
        } else {

            DB::table('course_stats')->insert([
                'id' => Str::uuid(),
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'specialty_id' => $specialtyId,
                'department_id' => $departmentId,
                'integer_value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
